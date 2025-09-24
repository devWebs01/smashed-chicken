<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Product;
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
        $data = $request->all();

        $sender = $data['sender'] ?? null;
        $message = strtolower($data['message'] ?? '');
        $devicePhone = $data['device'] ?? null;

        Log::info('Webhook received', $data);

        if (! $sender || ! $devicePhone) {
            return response()->json(['status' => 'error'], 400);
        }

        // Get device token from database
        $device = Device::where('device', $devicePhone)->first();
        if (! $device) {
            Log::error('Device not found for phone: '.$devicePhone);

            return response()->json(['status' => 'error'], 400);
        }
        $deviceToken = $device->token;

        // Parse message
        if (str_contains($message, 'menu') || str_contains($message, 'produk')) {
            $this->sendProductMenu($sender, $deviceToken);
        } else {
            // Default reply
            $this->sendDefaultReply($sender, $deviceToken);
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendProductMenu($phoneNumber, $deviceToken)
    {
        $products = Product::all();

        $message = "*Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1).". *{$product->name}* - Rp ".number_format($product->price, 0, ',', '.')."\n";
            $message .= "   {$product->description}\n\n";
        }

        $message .= 'Ketik nomor produk untuk memesan.';

        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }

    private function sendDefaultReply($phoneNumber, $deviceToken)
    {
        $message = "Halo! Ketik 'menu' untuk melihat daftar produk kami.";
        $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message, $deviceToken);
    }
}
