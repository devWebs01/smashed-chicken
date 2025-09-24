<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\FonnteService;
use App\Services\OrderParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $fonnteService;
    protected $orderParser;

    public function __construct(FonnteService $fonnteService, OrderParser $orderParser)
    {
        $this->fonnteService = $fonnteService;
        $this->orderParser = $orderParser;
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
        Cache::put($dedupKey, true, 300); // 5 minutes

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
                $message = "Selamat datang! Silakan perkenalkan diri Anda.\n\nKetik nama lengkap Anda:";
                Cache::put('customer_'.$phoneKey, 'await_name', 3600); // 1 hour
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            if ($customerStep === 'await_name') {
                $customerName = trim($message);
                if (empty($customerName)) {
                    $message = 'Nama tidak boleh kosong. Silakan ketik nama lengkap Anda:';
                    $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                    return;
                }
                Cache::put('customer_name_'.$phoneKey, $customerName, 3600);
                Cache::put('customer_'.$phoneKey, 'await_address', 3600);
                $message = "Terima kasih, {$customerName}!\n\nSilakan kirim alamat lengkap Anda untuk pengiriman (jika diperlukan):";
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            if ($customerStep === 'await_address') {
                $customerAddress = trim($message);
                Cache::put('customer_address_'.$phoneKey, $customerAddress, 3600);
                Cache::put('customer_'.$phoneKey, 'info_complete', 3600);
                $message = "Terima kasih! Data Anda telah tersimpan.\n\nSekarang Anda bisa mulai memesan.\nKetik 'menu' untuk melihat daftar produk.";
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }

            // Keywords for menu
            $menuKeywords = ['menu', 'produk', 'pesan', 'geprek', 'makan', 'order'];

            // Handle special commands
            if (strtolower($message) === 'tambah') {
                $this->handleTambah($data, $sender, $deviceToken);

                return;
            } elseif (strtolower($message) === 'selesai') {
                $phoneKey = $this->canonicalPhone($sender);
                Cache::forget('add_to_order_'.$phoneKey);
                $message = "Order selesai. Terima kasih!\nKetik 'menu' jika ingin pesan lagi.";
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            } elseif (strtolower($message) === 'reset') {
                $phoneKey = $this->canonicalPhone($sender);
                Cache::forget('order_'.$phoneKey);
                Cache::forget('add_to_order_'.$phoneKey);
                Cache::forget('customer_'.$phoneKey);
                Cache::forget('customer_name_'.$phoneKey);
                Cache::forget('customer_address_'.$phoneKey);
                $message = "Data cache direset. Silakan mulai dari awal.\nKetik 'halo' untuk perkenalan.";
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            } elseif (str_starts_with(strtolower($message), 'batal ')) {
                $parts = explode(' ', $message);
                if (count($parts) == 2 && is_numeric($parts[1])) {
                    $this->handleBatal($sender, $deviceToken, (int) $parts[1]);
                } else {
                    $this->sendDefaultReply($sender, $deviceToken);
                }

                return;
            } elseif (strtolower($message) === 'batal') {
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
            } elseif (in_array($message, ['ya', 'y', 'yes', 'iya', 'ok', 'konfirmasi'])) {
                $this->confirmOrder($data, $sender, $deviceToken);
            } elseif (in_array($message, ['edit', 'ubah', 'change', 'ganti'])) {
                $this->editOrder($sender, $deviceToken);
            } else {
                // --- ROUTE ORDER STEP IF EXISTS ---
                // Normalisasi key phone for cache
                $phoneKey = $this->canonicalPhone($sender);
                $orderCacheKey = 'order_'.$phoneKey;

                // Jika ada order in cache, panggil confirmOrder untuk step state
                if (Cache::has($orderCacheKey)) {
                    $this->confirmOrder($data, $sender, $deviceToken);

                    return response()->json(['status' => 'ok']);
                }

                // Try to parse product selections
                $selections = $this->orderParser->parse($message);
                if (! empty($selections)) {
                    $this->handleMultipleSelections($data, $sender, $deviceToken, $selections);
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
        $message = "Maaf, pesan Anda tidak dapat dipahami.\n\n".
            "Ketik *menu* untuk melihat daftar produk kami.\n\n".
            "*Cara Pemesanan:*\n".
            "- Untuk 1 produk: Ketik nomor produk\n".
            "   Contoh: *1* (1 porsi produk 1)\n".
            "- Untuk jumlah lebih: nomor=jumlah\n".
            "   Contoh: *1=3* (3 porsi produk 1)\n".
            "- Untuk multiple produk dengan qty sama: 1,2=3 (produk 1 dan 2 masing-masing 3 porsi)\n".
            "   Contoh: *1,2=2* (2 porsi produk 1 + 2 porsi produk 2)\n".
            "- Untuk multiple dengan qty berbeda: pisahkan dengan spasi\n".
            "   Contoh: *1=2 3=1 12=2,4* (2 porsi produk 1, 1 porsi produk 3, 2 porsi produk 12, 1 porsi produk 4)\n\n".
            "*Perintah Lain:*\n".
            "- *tambah* - Tambah produk ke pesanan terakhir\n".
            "- *batal* - Lihat pesanan pending untuk dibatalkan\n".
            "- *ya* - Konfirmasi pesanan\n".
            "- *edit* - Batalkan pesanan dan mulai ulang\n\n".
            'Silakan coba lagi atau ketik: menu';

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleProductSelection($data, $phoneNumber, $deviceToken, $productIndex, $quantity = 1)
    {
        $products = Product::all();

        if ($productIndex < 1 || $productIndex > $products->count()) {
            $message = "Maaf, nomor produk *{$productIndex}* tidak valid atau tidak tersedia.\nKetik *menu* untuk melihat daftar produk yang tersedia.";
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

    private function confirmOrder($data, $phoneNumber, $deviceToken)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = 'Tidak ada pesanan yang pending. Ketik menu untuk mulai pesan.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        // Check timeout (10 minutes)
        $createdAt = $orderData['created_at'] ?? now()->timestamp;
        if (now()->timestamp - $createdAt > 600) {
            Cache::forget('order_'.$phoneKey);
            $message = 'Pesanan expired karena tidak ada aktivitas. Silakan mulai pesan baru dengan ketik menu.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $step = $orderData['step'] ?? 'await_confirmation';

        if ($step === 'await_confirmation') {
            // Ask for delivery method
            $message = "Pilih metode pengiriman:\n- Ketik 'takeaway' untuk ambil sendiri\n- Ketik 'delivery' untuk diantar";
            $orderData['step'] = 'await_delivery_method';
            Cache::put('order_'.$phoneKey, $orderData, 600);
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        if ($step === 'await_delivery_method') {
            $deliveryMethod = strtolower(trim($data['message']));
            if (! in_array($deliveryMethod, ['takeaway', 'delivery'])) {
                $message = "Metode pengiriman tidak valid. Ketik 'takeaway' atau 'delivery'.";
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            }
            $orderData['delivery_method'] = $deliveryMethod;
            if ($deliveryMethod === 'delivery') {
                $orderData['step'] = 'await_address';
                Cache::put('order_'.$phoneKey, $orderData, 600);
                $message = 'Silakan kirim alamat lengkap pengiriman.';
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            } else {
                $orderData['step'] = 'await_payment_method';
                Cache::put('order_'.$phoneKey, $orderData, 600);
                $this->askPaymentMethod($phoneNumber, $deviceToken);

                return;
            }
        }

        if ($step === 'await_address') {
            $orderData['customer_address'] = trim($data['message']);
            $orderData['step'] = 'await_payment_method';
            Cache::put('order_'.$phoneKey, $orderData, 600);
            $this->askPaymentMethod($phoneNumber, $deviceToken);

            return;
        }

        if ($step === 'await_payment_method') {
            $paymentMethod = strtolower(trim($data['message']));
            if (! in_array($paymentMethod, ['cash', 'transfer'])) {
                $message = "Metode pembayaran tidak valid. Ketik 'cash' atau 'transfer'.";
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

                return;
            }
            $orderData['payment_method'] = $paymentMethod;
            $orderData['step'] = 'ready_to_confirm';
            Cache::put('order_'.$phoneKey, $orderData, 600);
            $this->showFinalReview($phoneNumber, $deviceToken, $orderData);

            return;
        }

        if ($step === 'ready_to_confirm') {
            $this->finalizeOrder($phoneNumber, $deviceToken, $orderData);
        }
    }

    private function askPaymentMethod($phoneNumber, $deviceToken)
    {
        $message = "Pilih metode pembayaran:\n- Ketik 'cash' untuk bayar tunai\n- Ketik 'transfer' untuk transfer bank";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function showFinalReview($phoneNumber, $deviceToken, $orderData)
    {
        $message = "*Review Pesanan Akhir:*\n\n";
        foreach ($orderData['selections'] as $sel) {
            $message .= "*{$sel['product_name']}* - {$sel['quantity']} porsi\n";
        }
        $message .= "\nPengiriman: {$orderData['delivery_method']}";
        if (isset($orderData['customer_address'])) {
            $message .= "\nAlamat: {$orderData['customer_address']}";
        }
        $message .= "\nPembayaran: {$orderData['payment_method']}";
        $message .= "\n*Total: Rp ".number_format($orderData['total'], 0, ',', '.')."*\n\n";
        $message .= "Ketik 'ya', 'y', 'yes', 'ok' untuk konfirmasi akhir\n";
        $message .= "Ketik 'edit', 'ubah' untuk mengubah pesanan.";
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
            $message = "Maaf, terjadi kesalahan saat memproses pesanan Anda.\nSilakan coba lagi dalam beberapa saat atau hubungi admin untuk bantuan.";
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
            $message = 'Tidak ada pesanan yang pending. Ketik menu untuk mulai pesan.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        // Clear cache
        Cache::forget('order_'.$phoneKey);

        $message = "Pesanan dibatalkan. Silakan pilih produk lagi.\nKetik *menu* untuk melihat daftar produk.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }


    private function handleMultipleSelections($data, $phoneNumber, $deviceToken, $selections)
    {
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $addToOrderId = Cache::get('add_to_order_'.$phoneKey);

        if ($addToOrderId) {
            // Add to existing order
            $this->addToExistingOrder($phoneNumber, $deviceToken, $selections, $addToOrderId);
            Cache::forget('add_to_order_'.$phoneKey);

            return;
        }

        $products = Product::all();
        $selectionMap = [];
        $total = 0;
        $reviewMessage = "*Review Pesanan Anda:*\n\n";

        foreach ($selections as $sel) {
            if ($sel['index'] < 1 || $sel['index'] > $products->count()) {
                continue; // Skip invalid
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
            $message = "Maaf, tidak ada produk valid dalam pesanan Anda.\nNomor produk mungkin tidak tersedia atau format salah.\n\nKetik *menu* untuk melihat daftar produk yang tersedia.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        foreach ($validSelections as $sel) {
            $reviewMessage .= "*{$sel['product_name']}* - {$sel['quantity']} porsi - Rp ".number_format($sel['subtotal'], 0, ',', '.')."\n";
            $total += $sel['subtotal'];
        }

        $reviewMessage .= "\n*Total Keseluruhan: Rp ".number_format($total, 0, ',', '.')."*\n\n";
        $reviewMessage .= "Apakah sudah benar?\n";
        $reviewMessage .= "- Ketik 'ya', 'y', 'yes', 'ok' untuk konfirmasi pesanan\n";
        $reviewMessage .= "- Ketik 'edit', 'ubah' untuk ubah pesanan\n";
        $reviewMessage .= "- Ketik 'menu' untuk lihat menu lagi";

        // Store selections in cache
        $phoneKey = $this->canonicalPhone($phoneNumber);
        $customerName = Cache::get('customer_name_'.$phoneKey, $data['name'] ?? 'Customer');
        Cache::put('order_'.$phoneKey, [
            'selections' => $validSelections,
            'total' => $total,
            'customer_name' => $customerName,
            'step' => 'await_confirmation',
            'created_at' => now()->timestamp,
        ], 600);

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $reviewMessage, $deviceToken);
    }

    private function addToExistingOrder($phoneNumber, $deviceToken, $selections, $orderId)
    {
        $order = Order::find($orderId);
        if (! $order) {
            $message = 'Pesanan tidak ditemukan.';
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
        // Ensure model has latest value
        $order->refresh();

        // Set flag for auto-add next messages (TTL 30 minutes)
        $phoneKey = $this->canonicalPhone($phoneNumber);
        Cache::put('add_to_order_'.$phoneKey, $order->id, 1800);

        $message = 'Produk berhasil ditambahkan ke Order #'.$order->id.":\n".implode(', ', $addedItems)."\n\nTotal sekarang: Rp ".number_format($order->total_price, 0, ',', '.')."\n\nUntuk menambah produk lagi, kirim nomor/format produk sekarang.\nKetik 'selesai' untuk selesai atau 'tambah' untuk order baru.";
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
            $message = 'Anda tidak memiliki pesanan pending.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $message = "*Pesanan Pending Anda:*\n\n";
        foreach ($pendingOrders as $index => $order) {
            $message .= ($index + 1).'. Order #'.$order->id.' - Total: Rp '.number_format($order->total_price, 0, ',', '.')."\n";
            foreach ($order->orderItems as $item) {
                $message .= "   • {$item->product->name} x{$item->quantity}\n";
            }
            $message .= "\n";
        }
        $message .= "Ketik *batal [nomor]* untuk membatalkan pesanan.\nContoh: *batal 1*";

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
            $message = 'Nomor pesanan tidak valid.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $order = $pendingOrders->get($orderIndex - 1);
        $order->update(['status' => 'cancelled']);

        $message = 'Pesanan #'.$order->id.' telah dibatalkan.';
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
            $message = 'Anda tidak memiliki pesanan pending. Ketik menu untuk mulai pesan baru.';
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);

            return;
        }

        $message = "*Pesanan Terakhir Anda:*\n\n";
        $message .= 'Order #'.$lastOrder->id.' - Total: Rp '.number_format($lastOrder->total_price, 0, ',', '.')."\n";
        foreach ($lastOrder->orderItems as $item) {
            $message .= "• {$item->product->name} x{$item->quantity}\n";
        }
        $message .= "\nKirim produk tambahan dengan format yang sama.\nContoh: *3=1* untuk tambah 1 porsi produk 3";

        // Store order id in cache for adding
        Cache::put('add_to_order_'.$phoneKey, $lastOrder->id, 600);

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }
}
