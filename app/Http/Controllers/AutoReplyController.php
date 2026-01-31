<?php

namespace App\Http\Controllers;

use App\Services\FonnteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutoReplyController extends Controller
{
    public function __construct(
        private readonly FonnteService $fonnteService
    ) {}

    /**
     * Handle incoming webhook from Fonnte
     * Reply to any message with a simple auto-reply
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Handle GET request - return webhook status
        if ($request->isMethod('GET')) {
            return response()->json([
                'status' => 'active',
                'message' => 'Auto-Reply Webhook is ready',
                'supported_methods' => ['GET', 'POST'],
                'endpoint' => '/webhook/autoreply',
                'description' => 'Send POST request with Fonnte webhook payload for auto-reply',
                'features' => [
                    'instant_reply' => true,
                    'smart_responses' => true,
                    'device_auto_sync' => true,
                ],
            ], 200);
        }

        Log::info('AutoReply Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
        ]);

        try {
            $data = $request->all();

            // Only process text messages
            if (($data['type'] ?? 'text') !== 'text') {
                Log::info('Ignoring non-text message', ['type' => $data['type'] ?? 'unknown']);

                return response()->json(['status' => 'ignored'], 200);
            }

            // Check required fields
            if (! isset($data['sender']) || ! isset($data['device'])) {
                Log::error('Missing required fields', ['data' => $data]);

                return response()->json(['status' => 'error', 'message' => 'Missing sender or device'], 200);
            }

            $sender = $data['sender'];
            $devicePhone = $data['device'];
            $message = $data['message'] ?? '';

            Log::info('Processing message for auto-reply', [
                'sender' => $sender,
                'device' => $devicePhone,
                'message' => $message,
            ]);

            // Find device token
            $device = $this->findDeviceToken($devicePhone);
            if (! $device) {
                Log::error('Device not found', ['device_phone' => $devicePhone]);

                return response()->json(['status' => 'error', 'message' => 'Device not found'], 200);
            }

            // Generate auto-reply message based on incoming message
            $replyMessage = $this->generateAutoReply($message);

            // Send reply
            $result = $this->fonnteService->sendWhatsAppMessage(
                $sender,
                $replyMessage,
                $device->token
            );

            Log::info('Auto-reply sent', [
                'sender' => $sender,
                'reply' => $replyMessage,
                'result' => $result,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Auto-reply sent',
                'reply' => $replyMessage,
                'fonnte_result' => $result,
            ], 200);

        } catch (\Exception $exception) {
            Log::error('AutoReply Exception', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // Return 200 to prevent Fonnte from retrying
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 200);
        }
    }

    /**
     * Find device token in database or sync from Fonnte
     */
    private function findDeviceToken(string $devicePhone)
    {
        // Normalize phone number
        $normalizedPhone = $this->normalizePhone($devicePhone);
        $altPhone = $this->altPhoneFormat($devicePhone);

        // Try to find in database
        $device = \App\Models\Device::where('device', $normalizedPhone)
            ->orWhere('device', $altPhone)
            ->first();

        if ($device) {
            return $device;
        }

        // Try to sync from Fonnte
        $result = $this->fonnteService->getAllDevices();
        if ($result['status'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $fonnteDevice) {
                $fonntePhone = $fonnteDevice['device'];
                $fonnteNormalized = $this->normalizePhone($fonntePhone);

                if ($fonnteNormalized === $normalizedPhone || $fonnteNormalized === $altPhone) {
                    return \App\Models\Device::create([
                        'name' => 'Auto-synced Device',
                        'device' => $fonnteNormalized,
                        'token' => $fonnteDevice['token'],
                    ]);
                }
            }
        }

        return null;
    }

    /**
     * Normalize phone number to international format
     */
    private function normalizePhone(string $phone): string
    {
        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;
    }

    /**
     * Get alternative phone format
     */
    private function altPhoneFormat(string $phone): string
    {
        return str_starts_with($phone, '62') ? '0'.substr($phone, 2) : $phone;
    }

    /**
     * Generate auto-reply message based on incoming message
     */
    private function generateAutoReply(string $message): string
    {
        $lowerMessage = strtolower(trim($message));

        // Specific responses
        if (in_array($lowerMessage, ['hai', 'halo', 'hello', 'hi'])) {
            return "Halo! Terima kasih sudah menghubungi layanan kami. 🙏\n\nBot ini sedang dalam pengembangan. Silakan tunggu balasan dari tim kami.";
        }

        if (in_array($lowerMessage, ['help', 'bantuan'])) {
            return "🤖 Bot Auto-Reply\n\nSaya adalah bot otomatis yang sedang dalam pengembangan. Untuk bantuan, silakan hubungi:\n• Admin: +62 859-5157-2182\n• Email: support@geprek.com";
        }

        if (in_array($lowerMessage, ['menu', 'produk'])) {
            return "📋 Menu Kami:\n\n1. Ayam Geprek Original\n2. Ayam Geprek Pedas\n3. Ayam Geprek Keju\n4. Ayam Geprek Sambal Matah\n5. Ayam Geprek Telur\n\nHarga: Rp 25.000 - Rp 35.000\n\nUntuk order, silakan tunggu admin yang akan membantu Anda.";
        }

        // Default response
        $responses = [
            "Terima kasih pesan Anda! 📝\n\nSedang menghubungi admin untuk membantu Anda. Mohon tunggu sebentar ya.",
            "Pesan Anda sudah diterima! 💬\n\nAdmin kami akan segera merespons pesan Anda. Terima kasih.",
            "Bot otomatis: Pesan Anda sudah kami catat. 🤖\n\nTim admin akan segera menghubungi Anda kembali.",
            "Terima kasih sudah chat! 📱\n\nMohan tunggu, admin sedang mempersiapkan jawaban untuk Anda.",
            "Pesan masuk terima kasih! ✅\n\nSistem kami sedang menghubungi admin untuk membalas pesan Anda.",
        ];

        return $responses[array_rand($responses)];
    }
}
