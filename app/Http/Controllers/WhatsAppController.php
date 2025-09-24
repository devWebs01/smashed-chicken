<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function handleWebhook(Request $request)
    {
        // Handle GET requests (for verification)
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok']);
        }

        $data = $request->all();

        $sender = $data['sender'] ?? null;
        $message = strtolower($data['message'] ?? '');
        $devicePhone = $data['device'] ?? null;
        $inboxId = $data['inboxid'] ?? null;
        $timestamp = $data['timestamp'] ?? null;

        // Idempotency check
        $messageHash = md5($sender . $message . $timestamp . ($inboxId ?? ''));
        if (Cache::has('processed_' . $messageHash)) {
            return response()->json(['status' => 'duplicate'], 200);
        }
        Cache::put('processed_' . $messageHash, true, 300); // 5 minutes

        Log::info('Webhook received', $data);

        if (! $sender || ! $devicePhone) {
            return response()->json(['status' => 'error'], 400);
        }

        // Normalize device phone number for querying
        $normalizedDevice = str_starts_with($devicePhone, '0') ? '62' . substr($devicePhone, 1) : $devicePhone;
        $altDevice = str_starts_with($devicePhone, '62') ? '0' . substr($devicePhone, 2) : $devicePhone;

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
                    $fonnteNormalized = str_starts_with($fonntePhone, '0') ? '62' . substr($fonntePhone, 1) : $fonntePhone;
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
                Log::error('Device not found and could not sync from Fonnte: ' . $devicePhone);
                return response()->json(['status' => 'error'], 400);
            }
        }
        $deviceToken = $device->token;

        try {
            // Check customer info step
            $customerStep = Cache::get('customer_' . $sender);
            if (!$customerStep) {
                // First time, ask for name
                $message = "Selamat datang! Silakan perkenalkan diri Anda.\n\nKetik nama lengkap Anda:";
                Cache::put('customer_' . $sender, 'await_name', 3600); // 1 hour
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
                return;
            }

            if ($customerStep === 'await_name') {
                $customerName = trim($message);
                if (empty($customerName)) {
                    $message = "Nama tidak boleh kosong. Silakan ketik nama lengkap Anda:";
                    $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
                    return;
                }
                Cache::put('customer_name_' . $sender, $customerName, 3600);
                Cache::put('customer_' . $sender, 'await_address', 3600);
                $message = "Terima kasih, {$customerName}!\n\nSilakan kirim alamat lengkap Anda untuk pengiriman (jika diperlukan):";
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
                return;
            }

            if ($customerStep === 'await_address') {
                $customerAddress = trim($message);
                Cache::put('customer_address_' . $sender, $customerAddress, 3600);
                Cache::put('customer_' . $sender, 'info_complete', 3600);
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
            } elseif (strtolower($message) === 'ya') {
                $this->confirmOrder($data, $sender, $deviceToken);
            } elseif (strtolower($message) === 'edit') {
                $this->editOrder($sender, $deviceToken);
            } else {
                // Try to parse product selections
                $selections = $this->parseProductSelections($message);
                if (!empty($selections)) {
                    $this->handleMultipleSelections($data, $sender, $deviceToken, $selections);
                } else {
                    // Default reply
                    $this->sendDefaultReply($sender, $deviceToken);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            $this->sendDefaultReply($sender, $deviceToken);
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendProductMenu($phoneNumber, $deviceToken)
    {
        $products = Product::all();

        $message = "*Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1) . ". *{$product->name}* - Rp " . number_format($product->price, 0, ',', '.') . "\n";
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
        $message .= "Ketik menu kapan saja untuk melihat daftar produk lagi.";

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function sendDefaultReply($phoneNumber, $deviceToken)
    {
        $message = "Halo! Selamat datang di *Layanan Pemesanan Online*.\n\n" .
            "Ketik menu untuk melihat daftar produk kami.\n\n" .
            "*Cara Pemesanan:*\n" .
            "- Untuk 1 produk: Ketik nomor produk\n" .
            "   Contoh: 1 (1 porsi produk 1)\n" .
            "- Untuk jumlah lebih: nomor=jumlah\n" .
            "   Contoh: 1=3 (3 porsi produk 1)\n" .
            "- Untuk multiple produk: pisahkan dengan koma\n" .
            "   Contoh: 1=2, 2=1 (2 porsi produk 1 + 1 porsi produk 2)\n\n" .
            "*Perintah Lain:*\n" .
            "- tambah - Tambah produk ke pesanan terakhir\n" .
            "- batal - Lihat pesanan pending untuk dibatalkan\n\n" .
            "Silakan mulai dengan mengetik: menu";


        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleProductSelection($data, $phoneNumber, $deviceToken, $productIndex, $quantity = 1)
    {
        $products = Product::all();

        if ($productIndex < 1 || $productIndex > $products->count()) {
            $message = "Nomor produk tidak valid.\nKetik *menu* untuk melihat daftar produk.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $product = $products->get($productIndex - 1);

        $subtotal = $product->price * $quantity;

        // Store selection in cache for 10 minutes (consistent structure)
        $customerName = Cache::get('customer_name_' . $phoneNumber, $data['name'] ?? 'Customer');
        Cache::put('order_' . $phoneNumber, [
            'selections' => [[
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->price,
                'subtotal' => $subtotal,
            ]],
            'total' => $subtotal,
            'customer_name' => $customerName,
        ], 600); // 10 minutes

        // Send review message
        $message = "*Review Pesanan Anda:*\n\n" .
            "Produk: *{$product->name}*\n" .
            "Jumlah: *{$quantity} porsi*\n" .
            "Total: *Rp " . number_format($subtotal, 0, ',', '.') . "*\n\n" .
            "Apakah sudah benar?\n" .
            "- Ketik *ya* untuk konfirmasi pesanan\n" .
            "- Ketik *edit* untuk ubah pesanan\n" .
            "- Ketik *menu* untuk lihat menu lagi";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function confirmOrder($data, $phoneNumber, $deviceToken)
    {
        $orderData = Cache::get('order_' . $phoneNumber);

        if (!$orderData) {
            $message = "Tidak ada pesanan yang pending. Ketik menu untuk mulai pesan.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $step = $orderData['step'] ?? 'await_confirmation';

        if ($step === 'await_confirmation') {
            // Ask for delivery method
            $message = "Pilih metode pengiriman:\n- Ketik 'takeaway' untuk ambil sendiri\n- Ketik 'delivery' untuk diantar";
            $orderData['step'] = 'await_delivery_method';
            Cache::put('order_' . $phoneNumber, $orderData, 600);
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        if ($step === 'await_delivery_method') {
            $deliveryMethod = strtolower(trim($data['message']));
            if (!in_array($deliveryMethod, ['takeaway', 'delivery'])) {
                $message = "Metode pengiriman tidak valid. Ketik 'takeaway' atau 'delivery'.";
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
                return;
            }
            $orderData['delivery_method'] = $deliveryMethod;
            if ($deliveryMethod === 'delivery') {
                $orderData['step'] = 'await_address';
                Cache::put('order_' . $phoneNumber, $orderData, 600);
                $message = "Silakan kirim alamat lengkap pengiriman.";
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
                return;
            } else {
                $orderData['step'] = 'await_payment_method';
                Cache::put('order_' . $phoneNumber, $orderData, 600);
                $this->askPaymentMethod($phoneNumber, $deviceToken);
                return;
            }
        }

        if ($step === 'await_address') {
            $orderData['customer_address'] = trim($data['message']);
            $orderData['step'] = 'await_payment_method';
            Cache::put('order_' . $phoneNumber, $orderData, 600);
            $this->askPaymentMethod($phoneNumber, $deviceToken);
            return;
        }

        if ($step === 'await_payment_method') {
            $paymentMethod = strtolower(trim($data['message']));
            if (!in_array($paymentMethod, ['cash', 'transfer'])) {
                $message = "Metode pembayaran tidak valid. Ketik 'cash' atau 'transfer'.";
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
                return;
            }
            $orderData['payment_method'] = $paymentMethod;
            $orderData['step'] = 'ready_to_confirm';
            Cache::put('order_' . $phoneNumber, $orderData, 600);
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
        $message .= "\n*Total: Rp " . number_format($orderData['total'], 0, ',', '.') . "*\n\n";
        $message .= "Ketik 'ya' untuk konfirmasi akhir atau 'edit' untuk ubah.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function finalizeOrder($phoneNumber, $deviceToken, $orderData)
    {
        try {
            DB::transaction(function () use ($orderData, $phoneNumber) {
                // Create order
                $customerAddress = $orderData['customer_address'] ?? Cache::get('customer_address_' . $phoneNumber);
                $order = Order::create([
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $phoneNumber,
                    'customer_address' => $customerAddress,
                    'status' => 'pending',
                    'order_date_time' => now(),
                    'total_price' => $orderData['total'],
                    'payment_method' => $orderData['payment_method'],
                    'delivery_method' => $orderData['delivery_method'],
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
            $message = $e->getMessage() . "\nSilakan coba lagi atau hubungi admin.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $confirmationMessage = "*Pesanan Dikonfirmasi!*\n\n";
        foreach ($orderData['selections'] as $sel) {
            $confirmationMessage .= "*{$sel['product_name']}* - {$sel['quantity']} porsi\n";
        }
        $confirmationMessage .= "\nPengiriman: {$orderData['delivery_method']}";
        if (isset($orderData['customer_address'])) {
            $confirmationMessage .= "\nAlamat: {$orderData['customer_address']}";
        }
        $confirmationMessage .= "\nPembayaran: {$orderData['payment_method']}";
        $confirmationMessage .= "\n*Total: Rp " . number_format($orderData['total'], 0, ',', '.') . "*\n\n";
        $confirmationMessage .= "Terima kasih telah memesan!\n";
        $confirmationMessage .= "Pesanan Anda sedang diproses. Kami akan segera menghubungi Anda untuk detail pengambilan/pengiriman.";

        // Clear cache
        Cache::forget('order_' . $phoneNumber);

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $confirmationMessage, $deviceToken);
    }

    private function editOrder($phoneNumber, $deviceToken)
    {
        $orderData = Cache::get('order_' . $phoneNumber);

        if (!$orderData) {
            $message = "Tidak ada pesanan yang pending. Ketik *menu* untuk mulai pesan.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        // Clear cache
        Cache::forget('order_' . $phoneNumber);

        $message = "Pesanan dibatalkan. Silakan pilih produk lagi.\nKetik *menu* untuk melihat daftar produk.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function parseProductSelections($message)
    {
        $selections = [];
        // Split by comma or newline
        $lines = preg_split('/[,|\n]/', $message);

        foreach ($lines as $line) {
            $line = trim($line);
            // Match number=quantity or number quantity
            if (preg_match('/(\d+)\s*[= ]\s*(\d+)/', $line, $matches)) {
                $productIndex = (int) $matches[1];
                $quantity = (int) $matches[2];
                if ($quantity > 0) {
                    $selections[] = ['index' => $productIndex, 'quantity' => $quantity];
                }
            }
        }

        return $selections;
    }

    private function handleMultipleSelections($data, $phoneNumber, $deviceToken, $selections)
    {
        $addToOrderId = Cache::get('add_to_order_' . $phoneNumber);

        if ($addToOrderId) {
            // Add to existing order
            $this->addToExistingOrder($phoneNumber, $deviceToken, $selections, $addToOrderId);
            Cache::forget('add_to_order_' . $phoneNumber);
            return;
        }

        $products = Product::all();
        $validSelections = [];
        $total = 0;
        $reviewMessage = "*Review Pesanan Anda:*\n\n";

        foreach ($selections as $sel) {
            if ($sel['index'] < 1 || $sel['index'] > $products->count()) {
                continue; // Skip invalid
            }
            $product = $products->get($sel['index'] - 1);
            $subtotal = $product->price * $sel['quantity'];
            $total += $subtotal;
            $validSelections[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $sel['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
            $reviewMessage .= "*{$product->name}* - {$sel['quantity']} porsi - Rp " . number_format($subtotal, 0, ',', '.') . "\n";
        }

        if (empty($validSelections)) {
            $message = "Tidak ada produk valid dalam pesanan Anda.\nKetik menu untuk melihat daftar produk.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $reviewMessage .= "\n*Total Keseluruhan: Rp " . number_format($total, 0, ',', '.') . "*\n\n";
        $reviewMessage .= "Apakah sudah benar?\n";
        $reviewMessage .= "- Ketik ya untuk konfirmasi pesanan\n";
        $reviewMessage .= "- Ketik edit untuk ubah pesanan\n";
        $reviewMessage .= "- Ketik menu untuk lihat menu lagi";

        // Store selections in cache
        $customerName = Cache::get('customer_name_' . $phoneNumber, $data['name'] ?? 'Customer');
        Cache::put('order_' . $phoneNumber, [
            'selections' => $validSelections,
            'total' => $total,
            'customer_name' => $customerName,
            'step' => 'await_confirmation',
        ], 600);

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $reviewMessage, $deviceToken);
    }

    private function addToExistingOrder($phoneNumber, $deviceToken, $selections, $orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            $message = "Pesanan tidak ditemukan.";
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

        $message = "Produk berhasil ditambahkan ke Order #" . $order->id . ":\n" . implode(', ', $addedItems) . "\n\nTotal sekarang: Rp " . number_format($order->total_price, 0, ',', '.');
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function showPendingOrders($phoneNumber, $deviceToken)
    {
        $pendingOrders = Order::where('customer_phone', $phoneNumber)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $message = "Anda tidak memiliki pesanan pending.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $message = "*Pesanan Pending Anda:*\n\n";
        foreach ($pendingOrders as $index => $order) {
            $message .= ($index + 1) . ". Order #" . $order->id . " - Total: Rp " . number_format($order->total_price, 0, ',', '.') . "\n";
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
        $pendingOrders = Order::where('customer_phone', $phoneNumber)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orderIndex < 1 || $orderIndex > $pendingOrders->count()) {
            $message = "Nomor pesanan tidak valid.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $order = $pendingOrders->get($orderIndex - 1);
        $order->update(['status' => 'cancelled']);

        $message = "Pesanan #" . $order->id . " telah dibatalkan.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleTambah($data, $phoneNumber, $deviceToken)
    {
        $lastOrder = Order::where('customer_phone', $phoneNumber)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->latest()
            ->first();

        if (!$lastOrder) {
            $message = "Anda tidak memiliki pesanan pending. Ketik menu untuk mulai pesan baru.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $message = "*Pesanan Terakhir Anda:*\n\n";
        $message .= "Order #" . $lastOrder->id . " - Total: Rp " . number_format($lastOrder->total_price, 0, ',', '.') . "\n";
        foreach ($lastOrder->orderItems as $item) {
            $message .= "• {$item->product->name} x{$item->quantity}\n";
        }
        $message .= "\nKirim produk tambahan dengan format yang sama.\nContoh: *3=1* untuk tambah 1 porsi produk 3";

        // Store order id in cache for adding
        Cache::put('add_to_order_' . $phoneNumber, $lastOrder->id, 600);

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }
}
