<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\FonnteService;
use App\Services\OrderParser;
use App\Services\WhatsAppOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    protected $orderParser;

    protected $orderService;

    public function __construct(
        FonnteService $fonnteService,
        OrderParser $orderParser,
        WhatsAppOrderService $orderService
    ) {
        $this->fonnteService = $fonnteService;
        $this->orderParser = $orderParser;
        $this->orderService = $orderService;
    }

    private function canonicalPhone($raw)
    {
        $p = preg_replace('/[^0-9]/', '', (string) $raw);
        // remove leading 0 -> 62...
        if (str_starts_with($p, '0')) {
            $p = '62'.substr($p, 1);
        }

        return $p;
    }

    public function handleWebhook(Request $request)
    {
        // Handle GET requests (for verification)
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok']);
        }

        $data = $request->all();

        $sender = $data['sender'] ?? null;
        $message = strtolower(trim($data['message'] ?? '', " \t\n\r\0\x0B'\""));
        $devicePhone = $data['device'] ?? null;
        $inboxId = $data['inboxid'] ?? null;
        $timestamp = $data['timestamp'] ?? null;

        // Idempotency check (use canonical phone + inboxid if available)
        $senderCanonical = $this->canonicalPhone($sender);
        $messageIdKey = $inboxId ?? ($data['message_id'] ?? null); // prefer provider message id if present
        if ($messageIdKey) {
            $dedupKey = 'processed_msgid_'.$messageIdKey;
        } else {
            // fallback: use canonical sender + message content hash (no timestamp)
            $dedupKey = 'processed_'.md5($senderCanonical.'|'.$message);
        }
        if (Cache::has($dedupKey)) {
            return response()->json(['status' => 'duplicate'], 200);
        }
        Cache::put($dedupKey, true, config('whatsapp.cache_ttl.dedup'));

        Log::info('Webhook received', $data);

        if (! $sender || ! $devicePhone) {
            return response()->json(['status' => 'error'], 400);
        }

        // Normalize device phone number for querying
        $normalizedDevice = str_starts_with($devicePhone, '0') ? '62'.substr($devicePhone, 1) : $devicePhone;
        $altDevice = str_starts_with($devicePhone, '62') ? '0'.substr($devicePhone, 2) : $devicePhone;

        // Get device token from database (try both formats)
        Log::info('Searching for device', ['original' => $devicePhone, 'normalized' => $normalizedDevice, 'alt' => $altDevice]);
        $device = Device::where('device', $normalizedDevice)->orWhere('device', $altDevice)->first();
        if (! $device) {
            // Try to sync from Fonnte
            Log::info('Device not found, trying to sync from Fonnte');
            $res = $this->fonnteService->getAllDevices();
            if ($res['status'] && isset($res['data']['data'])) {
                foreach ($res['data']['data'] as $fonnteDevice) {
                    $fonntePhone = $fonnteDevice['device'];
                    // Normalize Fonnte phone
                    $fonnteNormalized = str_starts_with($fonntePhone, '0') ? '62'.substr($fonntePhone, 1) : $fonntePhone;
                    if ($fonnteNormalized === $normalizedDevice || $fonnteNormalized === $altDevice) {
                        // Found, create in DB
                        $device = Device::create([
                            'name' => 'Auto-synced Device',
                            'device' => $fonnteNormalized,
                            'token' => $fonnteDevice['token'],
                        ]);
                        Log::info('Device auto-synced', ['device' => $fonnteNormalized, 'token' => $fonnteDevice['token']]);
                        break;
                    }
                }
            }
            if (! $device) {
                Log::error('Device not found and could not sync from Fonnte: '.$devicePhone);

                return response()->json(['status' => 'error'], 400);
            }
        }
        $deviceToken = $device->token;

        try {
            // Check customer info step
            $phoneKey = $this->canonicalPhone($sender);
            $customerStep = Cache::get('customer_'.$phoneKey);
            if (! $customerStep) {
                // First time, ask for name
                $message = config('whatsapp.messages.welcome');
                Cache::put('customer_'.$phoneKey, 'await_name', config('whatsapp.cache_ttl.customer_info'));
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            if ($customerStep === 'await_name') {
                $customerName = trim($message);
                if (empty($customerName)) {
                    $message = config('whatsapp.messages.name_empty');
                    $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                    return;
                }
                Cache::put('customer_name_'.$phoneKey, $customerName, config('whatsapp.cache_ttl.customer_info'));
                Cache::put('customer_'.$phoneKey, 'await_address', config('whatsapp.cache_ttl.customer_info'));
                $message = str_replace('{name}', $customerName, config('whatsapp.messages.address_prompt'));
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            if ($customerStep === 'await_address') {
                $customerAddress = trim($message);
                Cache::put('customer_address_'.$phoneKey, $customerAddress, config('whatsapp.cache_ttl.customer_info'));
                Cache::put('customer_'.$phoneKey, 'info_complete', config('whatsapp.cache_ttl.customer_info'));
                $message = config('whatsapp.messages.info_complete');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            // Keywords for menu
            $menuKeywords = config('whatsapp.keywords.menu', []);

            // Handle special commands
            if (in_array($message, config('whatsapp.keywords.tambah', []))) {
                $this->handleTambah($data, $sender, $deviceToken);

                return;
            } elseif (in_array($message, config('whatsapp.keywords.selesai', []))) {
                $phoneKey = $this->canonicalPhone($sender);
                Cache::forget('add_to_order_'.$phoneKey);
                $message = config('whatsapp.messages.order_complete');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            } elseif (in_array($message, config('whatsapp.keywords.reset', []))) {
                $phoneKey = $this->canonicalPhone($sender);
                Cache::forget('order_'.$phoneKey);
                Cache::forget('add_to_order_'.$phoneKey);
                Cache::forget('customer_'.$phoneKey);
                Cache::forget('customer_name_'.$phoneKey);
                Cache::forget('customer_address_'.$phoneKey);
                $message = config('whatsapp.messages.reset_done');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            } elseif (str_starts_with($message, 'batal ')) {
                $parts = explode(' ', $message);
                if (count($parts) == 2 && is_numeric($parts[1])) {
                    $this->handleBatal($sender, $deviceToken, (int) $parts[1]);
                } else {
                    $this->sendDefaultReply($sender, $deviceToken);
                }

                return;
            } elseif (in_array($message, config('whatsapp.keywords.batal', []))) {
                $this->showPendingOrders($sender, $deviceToken);

                return;
            }

            // Check if message contains any menu keyword
            $isMenuRequest = false;
            foreach ($menuKeywords as $keyword) {
                if (str_contains(strtolower($message), $keyword)) {
                    $isMenuRequest = true;
                    break;
                }
            }

            if ($isMenuRequest) {
                $this->sendProductMenu($sender, $deviceToken);
            } elseif (in_array($message, config('whatsapp.keywords.confirm', []))) {
                $this->handleConfirmOrder($data, $sender, $deviceToken);
            } elseif (in_array($message, config('whatsapp.keywords.edit', []))) {
                $this->editOrder($sender, $deviceToken);
            } else {
                // --- ROUTE ORDER STEP IF EXISTS ---
                // Normalisasi key phone for cache
                $phoneKey = $this->canonicalPhone($sender);
                $orderCacheKey = 'order_'.$phoneKey;

                // Jika ada order in cache, panggil confirmOrder untuk step state
                if (Cache::has($orderCacheKey)) {
                    $this->handleConfirmOrder($data, $sender, $deviceToken);

                    return response()->json(['status' => 'ok']);
                }

                // Try to parse product selections
                $selections = $this->orderParser->parse($message);
                if (! empty($selections)) {
                    $this->orderService->processSelections($selections, $data, $sender, $deviceToken);
                } else {
                    // Default reply
                    $this->sendDefaultReply($sender, $deviceToken);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook: '.$e->getMessage());
            $errorMessage = "Maaf, terjadi kesalahan sistem.\nSilakan coba lagi atau ketik *menu* untuk mulai pesan.";
            $this->fonnteService->sendWhatsAppMessage($sender, $errorMessage, $deviceToken);
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendProductMenu($phoneNumber, $deviceToken)
    {
        $products = Product::all();

        $message = "*Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1).". *{$product->name}* - Rp ".number_format($product->price, 0, ',', '.')."\n";
            if ($product->description) {
                $message .= "   {$product->description}\n\n";
            }
        }

        $message .= "----------------------------\n";
        $message .= "*Cara Pemesanan:*\n";
        $message .= "• Ketik nomor produk untuk pesan 1 porsi.\n";
        $message .= "   Contoh: *1*\n";
        $message .= "• Ketik nomor + jumlah untuk pesan lebih dari 1.\n";
        $message .= "   Contoh: *1 3* atau *1=3* (3 porsi produk 1)\n";
        $message .= "• Untuk pesan multiple produk, pisahkan dengan koma.\n";
        $message .= "   Contoh: *1=2, 2=1* (2 porsi produk 1 + 1 porsi produk 2)\n\n";
        $message .= 'Ketik menu kapan saja untuk melihat daftar produk lagi.';

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function sendDefaultReply($phoneNumber, $deviceToken)
    {
        $message = config('whatsapp.messages.default_reply');
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleProductSelection($data, $phoneNumber, $deviceToken, $productIndex, $quantity = 1)
    {
        $products = Product::all();

        if ($productIndex < 1 || $productIndex > $products->count()) {
            $message = str_replace('{number}', $productIndex, config('whatsapp.messages.invalid_product'));
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $product = $products->get($productIndex - 1);

        $subtotal = $product->price * $quantity;

        // Store selection in cache for 10 minutes (consistent structure)
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $customerName = Cache::get('customer_name_'.$phoneKey, $data['name'] ?? 'Customer');
        Cache::put('order_'.$phoneKey, [
            'selections' => [[
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'subtotal' => $subtotal,
            ]],
            'total' => $subtotal,
            'customer_name' => $customerName,
            'created_at' => now()->timestamp,
        ], 600); // 10 minutes

        // Send review message
        $message = "*Review Pesanan Anda:*\n\n".
            "Produk: *{$product->name}*\n".
            "Jumlah: *{$quantity} porsi*\n".
            'Total: *Rp '.number_format($subtotal, 0, ',', '.')."*\n\n".
            "Apakah sudah benar?\n".
            "- Ketik *ya* untuk konfirmasi pesanan\n".
            "- Ketik *edit* untuk ubah pesanan\n".
            '- Ketik *menu* untuk lihat menu lagi';
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleConfirmOrder($data, $phoneNumber, $deviceToken)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        // Check timeout
        $createdAt = $orderData['created_at'] ?? now()->timestamp;
        if (now()->timestamp - $createdAt > config('whatsapp.cache_ttl.order')) {
            Cache::forget('order_'.$phoneKey);
            $message = config('whatsapp.messages.order_expired');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $step = $orderData['step'] ?? 'await_confirmation';

        if ($step === 'await_confirmation') {
            // Ask for delivery method
            $message = config('whatsapp.messages.delivery_prompt');
            $orderData['step'] = 'await_delivery_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        if ($step === 'await_delivery_method') {
            $deliveryMethod = strtolower(trim($data['message']));
            if (! in_array($deliveryMethod, ['takeaway', 'delivery'])) {
                $message = config('whatsapp.messages.invalid_delivery');
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            }
            $orderData['delivery_method'] = $deliveryMethod;
            if ($deliveryMethod === 'delivery') {
                $orderData['step'] = 'await_address';
                Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
                $message = config('whatsapp.messages.address_prompt_delivery');
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            } else {
                $orderData['step'] = 'await_payment_method';
                Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
                $this->askPaymentMethod($phoneNumber, $deviceToken);

                return;
            }
        }

        if ($step === 'await_address') {
            $orderData['customer_address'] = trim($data['message']);
            $orderData['step'] = 'await_payment_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->askPaymentMethod($phoneNumber, $deviceToken);

            return;
        }

        if ($step === 'await_payment_method') {
            $paymentMethod = strtolower(trim($data['message']));
            if (! in_array($paymentMethod, ['cash', 'transfer'])) {
                $message = config('whatsapp.messages.invalid_payment');
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            }
            $orderData['payment_method'] = $paymentMethod;
            $orderData['step'] = 'ready_to_confirm';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->showFinalReview($phoneNumber, $deviceToken, $orderData);

            return;
        }

        if ($step === 'ready_to_confirm') {
            $this->finalizeOrder($phoneNumber, $deviceToken, $orderData);
        }
    }

    private function askPaymentMethod($phoneNumber, $deviceToken)
    {
        $message = config('whatsapp.messages.payment_prompt');
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function showFinalReview($phoneNumber, $deviceToken, $orderData)
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

    private function finalizeOrder($phoneNumber, $deviceToken, $orderData)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        try {
            DB::transaction(function () use ($orderData, $phoneKey, $deviceToken) {
                // Find device by token
                $device = Device::where('token', $deviceToken)->first();
                // Create order
                $customerAddress = $orderData['customer_address'] ?? Cache::get('customer_address_'.$phoneKey);
                $order = Order::create([
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $phoneKey, // <-- store canonical
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
        } catch (\Exception $e) {
            Log::error('Failed to finalize order: '.$e->getMessage());
            $message = config('whatsapp.messages.order_error');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        // Clear cache
        Cache::forget('order_'.$phoneKey);

        // Confirmation message will be sent by OrderObserver
    }

    private function editOrder($phoneNumber, $deviceToken)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        // Clear cache
        Cache::forget('order_'.$phoneKey);

        $message = config('whatsapp.messages.order_cancelled');
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }


    private function showPendingOrders($phoneNumber, $deviceToken)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $pendingOrders = Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $message = config('whatsapp.messages.no_pending_orders');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $ordersText = '';
        foreach ($pendingOrders as $index => $order) {
            $ordersText .= ($index + 1).'. Order #'.$order->id.' - Total: Rp '.number_format($order->total_price, 0, ',', '.')."\n";
            foreach ($order->orderItems as $item) {
                $ordersText .= "   • {$item->product->name} x{$item->quantity}\n";
            }
            $ordersText .= "\n";
        }

        $message = str_replace('{orders}', $ordersText, config('whatsapp.messages.pending_orders'));
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleBatal($phoneNumber, $deviceToken, $orderIndex)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $pendingOrders = Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orderIndex < 1 || $orderIndex > $pendingOrders->count()) {
            $message = config('whatsapp.messages.invalid_order_index');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $order = $pendingOrders->get($orderIndex - 1);
        $order->update(['status' => 'cancelled']);

        $message = str_replace('{id}', $order->id, config('whatsapp.messages.order_cancelled_success'));
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleTambah($data, $phoneNumber, $deviceToken)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $lastOrder = Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->latest()
            ->first();

        if (! $lastOrder) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $itemsText = '';
        foreach ($lastOrder->orderItems as $item) {
            $itemsText .= "• {$item->product->name} x{$item->quantity}\n";
        }

        $message = str_replace(
            ['{id}', '{total}', '{items}'],
            [$lastOrder->id, number_format($lastOrder->total_price, 0, ',', '.'), $itemsText],
            config('whatsapp.messages.last_order')
        );

        // Store order id in cache for adding
        Cache::put('add_to_order_'.$phoneKey, $lastOrder->id, config('whatsapp.cache_ttl.add_to_order'));

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }
}
