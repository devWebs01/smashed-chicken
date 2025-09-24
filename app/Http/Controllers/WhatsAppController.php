<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            Log::error('Device not found for phone: ' . $devicePhone . ' (tried ' . $normalizedDevice . ' and ' . $altDevice . ')');

            return response()->json(['status' => 'error'], 400);
        }
        $deviceToken = $device->token;

        try {
            // Keywords for menu
            $menuKeywords = ['menu', 'produk', 'pesan', 'geprek', 'makan', 'order'];

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
            } elseif (preg_match('/^(\d+)\s+(\d+)$/', $message, $matches)) {
                // Format: number space quantity, e.g., "1 2"
                $productIndex = (int) $matches[1];
                $quantity = (int) $matches[2];
                $this->handleProductSelection($data, $sender, $deviceToken, $productIndex, $quantity);
            } elseif (is_numeric($message)) {
                // Default to quantity 1
                $this->handleProductSelection($data, $sender, $deviceToken, (int) $message, 1);
            } else {
                // Default reply
                $this->sendDefaultReply($sender, $deviceToken);
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

        $message = "🍽️ *Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1) . ". *{$product->name}* - Rp " . number_format($product->price, 0, ',', '.') . "\n";
            if ($product->description) {
                $message .= "   📌 {$product->description}\n\n";
            }
        }

        $message .= "----------------------------\n";
        $message .= "📦 *Cara Pemesanan:*\n";
        $message .= "• Ketik nomor produk untuk pesan 1 porsi.\n";
        $message .= "   Contoh: *1*\n";
        $message .= "• Ketik nomor + jumlah untuk pesan lebih dari 1.\n";
        $message .= "   Contoh: *1 3* (3 porsi produk 1)\n";
        $message .= "• Untuk pesan produk lain, ketik pesan baru sesuai nomor.\n\n";
        $message .= "Ketik *menu* kapan saja untuk melihat daftar produk lagi. 😉";

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function sendDefaultReply($phoneNumber, $deviceToken)
    {
        $message = "👋 Halo! Selamat datang di *Layanan Pemesanan Online*.\n\n" .
            "Ketik *menu* untuk melihat daftar produk kami 📋.\n" .
            "Contoh pemesanan:\n" .
            "• Ketik *1* untuk pesan produk nomor 1 (1 porsi)\n" .
            "• Ketik *1 3* untuk pesan 3 porsi produk nomor 1\n\n" .
            "Silakan mulai dengan mengetik: *menu*";


        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function handleProductSelection($data, $phoneNumber, $deviceToken, $productIndex, $quantity = 1)
    {
        $products = Product::all();

        if ($productIndex < 1 || $productIndex > $products->count()) {
            $message = "⚠️ Nomor produk tidak valid.\nKetik *menu* untuk melihat daftar produk.";
            $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
            return;
        }

        $product = $products->get($productIndex - 1);

        $subtotal = $product->price * $quantity;

        // Create order
        $order = Order::create([
            'customer_name' => $data['name'] ?? 'Customer',
            'customer_phone' => $phoneNumber,
            'status' => 'pending',
            'order_date_time' => now(),
            'total_price' => $subtotal,
            'delivery_method' => 'takeaway',
        ]);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $subtotal,
        ]);

        // Pesan konfirmasi ke pelanggan
        $message = "✅ *Pesanan Diterima!*\n\n" .
                   "📌 Produk: *{$product->name}*\n" .
                   "🍽️ Jumlah: *{$quantity} porsi*\n" .
                   "💰 Total: *Rp " . number_format($subtotal, 0, ',', '.') . "*\n\n" .
                   "Terima kasih telah memesan! 🙏\n" .
                   "Pesanan Anda sedang diproses. Kami akan segera menghubungi Anda untuk detail pengambilan/pengiriman.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }
}
