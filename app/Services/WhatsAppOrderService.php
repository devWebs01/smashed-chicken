<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppOrderService
{
    public function __construct(
        private FonnteService $fonnteService
    ) {}

    /**
     * Process multiple product selections
     */
    public function processSelections(array $selections, array $data, string $phoneNumber, string $deviceToken): void
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $addToOrderId = Cache::get('add_to_order_'.$phoneKey);

        if ($addToOrderId) {
            $this->addToExistingOrder($phoneNumber, $deviceToken, $selections, $addToOrderId);
            Cache::forget('add_to_order_'.$phoneKey);

            return;
        }

        $products = Product::all();
        $selectionMap = [];
        $total = 0;
        $reviewMessage = config('whatsapp.messages.review');

        foreach ($selections as $sel) {
            if ($sel['index'] < 1 || $sel['index'] > $products->count()) {
                continue;
            }
            $product = $products->get($sel['index'] - 1);
            $pid = $product->id;
            if (! isset($selectionMap[$pid])) {
                $selectionMap[$pid] = [
                    'product_id' => $pid,
                    'product_name' => $product->name,
                    'quantity' => 0,
                    'price' => $product->price,
                    'subtotal' => 0,
                ];
            }
            $selectionMap[$pid]['quantity'] += $sel['quantity'];
            $selectionMap[$pid]['subtotal'] += $product->price * $sel['quantity'];
        }

        $validSelections = array_values($selectionMap);

        if (empty($validSelections)) {
            $message = config('whatsapp.messages.no_valid_products');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $itemsText = '';
        foreach ($validSelections as $sel) {
            $itemsText .= "*{$sel['product_name']}* - {$sel['quantity']} porsi - Rp ".number_format($sel['subtotal'], 0, ',', '.')."\n";
            $total += $sel['subtotal'];
        }

        $reviewMessage = str_replace(['{items}', '{total}'], [$itemsText, number_format($total, 0, ',', '.')], $reviewMessage);

        // Store selections in cache
        $customerName = Cache::get('customer_name_'.$phoneKey, $data['name'] ?? 'Customer');
        Cache::put('order_'.$phoneKey, [
            'selections' => $validSelections,
            'total' => $total,
            'customer_name' => $customerName,
            'step' => 'await_confirmation',
            'created_at' => now()->timestamp,
        ], config('whatsapp.cache_ttl.order'));

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $reviewMessage, $deviceToken);
    }

    /**
     * Confirm and process the order
     */
    public function confirmOrder(array $orderData, string $phoneNumber, string $deviceToken): array
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);

        // Check timeout
        $createdAt = $orderData['created_at'] ?? now()->timestamp;
        if (now()->timestamp - $createdAt > config('whatsapp.cache_ttl.order')) {
            Cache::forget('order_'.$phoneKey);

            return ['status' => 'expired', 'message' => config('whatsapp.messages.order_expired')];
        }

        $step = $orderData['step'] ?? 'await_confirmation';

        if ($step === 'await_confirmation') {
            $orderData['step'] = 'await_delivery_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));

            return ['status' => 'await_delivery', 'message' => config('whatsapp.messages.delivery_prompt')];
        }

        if ($step === 'await_delivery_method') {
            // This would be handled by the controller based on message content
            return ['status' => 'processing_delivery'];
        }

        if ($step === 'await_address') {
            // This would be handled by the controller
            return ['status' => 'processing_address'];
        }

        if ($step === 'await_payment_method') {
            // This would be handled by the controller
            return ['status' => 'processing_payment'];
        }

        if ($step === 'ready_to_confirm') {
            return $this->finalizeOrder($orderData, $phoneKey, $deviceToken);
        }

        return ['status' => 'unknown_step'];
    }

    /**
     * Finalize and create the order
     */
    private function finalizeOrder(array $orderData, string $phoneKey, string $deviceToken): array
    {
        try {
            DB::transaction(function () use ($orderData, $phoneKey, $deviceToken) {
                // Find device by token
                $device = \App\Models\Device::where('token', $deviceToken)->first();

                // Create order
                $customerAddress = $orderData['customer_address'] ?? Cache::get('customer_address_'.$phoneKey);
                $order = Order::create([
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $phoneKey,
                    'customer_address' => $customerAddress,
                    'status' => 'pending',
                    'order_date_time' => now(),
                    'total_price' => $orderData['total'],
                    'payment_method' => $orderData['payment_method'],
                    'delivery_method' => $orderData['delivery_method'],
                    'device_id' => $device ? $device->id : null,
                ]);

                // Create order items
                foreach ($orderData['selections'] as $sel) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $sel['product_id'],
                        'quantity' => $sel['quantity'],
                        'price' => $sel['price'],
                        'subtotal' => $sel['subtotal'],
                    ]);
                }
            });

            // Clear cache
            Cache::forget('order_'.$phoneKey);

            return ['status' => 'success', 'message' => 'Order created successfully'];

        } catch (\Exception $e) {
            Log::error('Failed to finalize order: '.$e->getMessage());

            return ['status' => 'error', 'message' => config('whatsapp.messages.order_error')];
        }
    }

    /**
     * Add selections to existing order
     */
    private function addToExistingOrder(string $phoneNumber, string $deviceToken, array $selections, int $orderId): void
    {
        $order = Order::find($orderId);
        if (! $order) {
            $message = config('whatsapp.messages.order_not_found');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $products = Product::all();
        $addedItems = [];
        $addedTotal = 0;

        foreach ($selections as $sel) {
            if ($sel['index'] < 1 || $sel['index'] > $products->count()) {
                continue;
            }
            $product = $products->get($sel['index'] - 1);
            $subtotal = $product->price * $sel['quantity'];
            $addedTotal += $subtotal;

            // Check if product already in order
            $existingItem = $order->orderItems()->where('product_id', $product->id)->first();
            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $sel['quantity'],
                    'subtotal' => $existingItem->subtotal + $subtotal,
                ]);
            } else {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $sel['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $addedItems[] = "{$product->name} x{$sel['quantity']}";
        }

        $order->update(['total_price' => $order->total_price + $addedTotal]);
        $order->refresh();

        // Set flag for auto-add next messages
        $phoneKey = $this->canonicalPhone($phoneNumber);
        Cache::put('add_to_order_'.$phoneKey, $order->id, config('whatsapp.cache_ttl.add_to_order'));

        $message = str_replace(
            ['{id}', '{items}', '{total}'],
            [$order->id, implode(', ', $addedItems), number_format($order->total_price, 0, ',', '.')],
            config('whatsapp.messages.products_added')
        );
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    /**
     * Cancel order
     */
    public function cancelOrder(string $phoneNumber, string $deviceToken): void
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        Cache::forget('order_'.$phoneKey);
        $message = config('whatsapp.messages.order_cancelled');
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    /**
     * Show final review
     */
    public function showFinalReview(array $orderData, string $phoneNumber, string $deviceToken): void
    {
        $itemsText = '';
        foreach ($orderData['selections'] as $sel) {
            $itemsText .= "*{$sel['product_name']}* - {$sel['quantity']} porsi\n";
        }

        $message = str_replace(
            ['{items}', '{delivery}', '{address}', '{payment}', '{total}'],
            [
                $itemsText,
                $orderData['delivery_method'],
                $orderData['customer_address'] ?? '',
                $orderData['payment_method'],
                number_format($orderData['total'], 0, ',', '.'),
            ],
            config('whatsapp.messages.final_review')
        );

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    /**
     * Canonical phone number
     */
    private function canonicalPhone(string $raw): string
    {
        $p = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($p, '0')) {
            $p = '62'.substr($p, 1);
        }

        return $p;
    }
}
