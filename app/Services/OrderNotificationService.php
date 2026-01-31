<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    public function __construct(protected \App\Services\FonnteService $fonnteService) {}

    /**
     * Send notification for new order
     */
    public function notifyNewOrder(Order $order): void
    {
        // Skip notification for web orders (no device_id)
        if (! $order->device_id) {
            Log::info('Skipping notification for web order', [
                'order_id' => $order->id,
                'customer_phone' => $order->customer_phone,
                'reason' => 'Web order - no device_id required',
            ]);

            return;
        }

        // Load order relationships
        $order->load('orderItems.product', 'device');

        // Get device for sending message
        $device = $order->device;
        if (! $device) {
            Log::warning('Device not found for new order notification', [
                'order_id' => $order->id,
                'device_id' => $order->device_id,
            ]);

            return;
        }

        // Get message template for new order
        $message = $this->getNewOrderMessage($order);
        if ($message === '' || $message === '0') {
            Log::info('No notification message for new order', [
                'order_id' => $order->id,
            ]);

            return;
        }

        try {
            $this->fonnteService->sendWhatsAppMessage($order->customer_phone, $message, $device->token);
            Log::info('New order notification sent', [
                'order_id' => $order->id,
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to send new order notification', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Send notification when order status changes
     */
    public function notifyStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        // Skip if status didn't actually change
        if ($oldStatus === $newStatus) {
            return;
        }

        // Skip notification for offline orders (no device_id)
        if (! $order->device_id) {
            Log::info('Skipping notification for offline order', [
                'order_id' => $order->id,
                'customer_phone' => $order->customer_phone,
                'status_change' => $oldStatus.' -> '.$newStatus,
                'reason' => 'No device_id - offline order',
            ]);

            return;
        }

        // Load order relationships
        $order->load('orderItems.product', 'device');

        // Get device for sending message
        $device = $order->device;
        if (! $device) {
            Log::warning('Device not found for order status notification', [
                'order_id' => $order->id,
                'device_id' => $order->device_id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);

            return;
        }

        // Get message template for status change
        $message = $this->getStatusChangeMessage($order, $newStatus);
        if (! $message) {
            Log::info('No notification message for status change', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);

            return;
        }

        try {
            $this->fonnteService->sendWhatsAppMessage($order->customer_phone, $message, $device->token);
            Log::info('Order status change notification sent', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to send order status notification', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get message template for new order
     */
    private function getNewOrderMessage(Order $order): string
    {
        $template = config('whatsapp.messages.new_order',
            "🛒 *Pesanan Baru #%s*\n\n".
            "📋 Detail Pesanan:\n%s\n".
            "🚚 Pengiriman: %s\n".
            "📍 Alamat: %s\n".
            "💳 Pembayaran: %s\n".
            "💰 Total: Rp %s\n\n".
            'Terima kasih atas pesanan Anda! 🙏'
        );

        // Build order items text
        $itemsText = '';
        foreach ($order->orderItems as $item) {
            $itemsText .= sprintf('• %s x%s%s', $item->product->name, $item->quantity, PHP_EOL);
        }

        // Format message
        $message = sprintf(
            $template,
            $order->id,
            $itemsText,
            $order->delivery_method === 'delivery' ? 'Delivery' : 'Take Away',
            $order->customer_address ?: 'Take Away - Ambil di tempat',
            $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : $order->payment_method,
            number_format($order->total_price, 0, ',', '.')
        );

        return $message;
    }

    /**
     * Get message template for status change
     */
    private function getStatusChangeMessage(Order $order, string $newStatus): ?string
    {
        $templateKey = 'status_'.$newStatus;

        // Special handling for cancelled status
        if ($newStatus === Order::STATUS_CANCELLED) {
            $templateKey = 'status_cancelled';
        }

        $template = config('whatsapp.messages.'.$templateKey);

        if (! $template) {
            return null;
        }

        // Build order items text
        $itemsText = '';
        foreach ($order->orderItems as $item) {
            $itemsText .= sprintf('• %s x%s%s', $item->product->name, $item->quantity, PHP_EOL);
        }

        // Replace placeholders
        $message = str_replace(
            [
                '{order_id}',
                '{items}',
                '{delivery}',
                '{address}',
                '{payment}',
                '{total}',
            ],
            [
                $order->id,
                $itemsText,
                $order->delivery_method,
                $order->customer_address ?: 'N/A',
                $order->payment_method,
                number_format($order->total_price, 0, ',', '.'),
            ],
            $template
        );

        return $message;
    }
}
