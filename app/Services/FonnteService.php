<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $account_token;

    // Konstanta endpoint API Fonnte
    const ENDPOINTS = [
        'send_message' => 'https://api.fonnte.com/send',
        'add_device' => 'https://api.fonnte.com/add-device',
        'qr_activation' => 'https://api.fonnte.com/qr',
        'get_devices' => 'https://api.fonnte.com/get-devices',
        'device_profile' => 'https://api.fonnte.com/device',
        'delete_device' => 'https://api.fonnte.com/delete-device',
        'disconnect' => 'https://api.fonnte.com/disconnect',
        // NEW ENDPOINTS from Fonnte Update 12 Jan 2026
        'reschedule' => 'https://api.fonnte.com/reschedule',
        'typing' => 'https://api.fonnte.com/typing',
        'statistics' => 'https://api.fonnte.com/statistics',
    ];

    public function __construct()
    {
        $this->account_token = env('ACCOUNT_TOKEN');
    }

    protected function makeRequest(string $endpoint, $params = [], $useAccountToken = true, $deviceToken = null): array
    {
        $token = $useAccountToken
            ? $this->account_token
            : ($deviceToken ?? null);

        if (! $token) {
            return ['status' => false, 'error' => 'API token or device token is required.'];
        }

        // Kirim sebagai JSON body
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => $token,
        ])->post($endpoint, $params);

        // Log respons untuk memudahkan debugging
        Log::info('Fonnte API Response', ['endpoint' => $endpoint, 'response' => $response->json()]);

        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->json()['reason'] ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $response->json(),
        ];
    }

    /**
     * Send WhatsApp message with optional parameters for reply feature
     *
     * @param  string  $phoneNumber  Target phone number
     * @param  string  $message  Message to send
     * @param  string  $deviceToken  Device token for authentication
     * @param  string|null  $inboxId  For reply-to-message feature (quote reply)
     * @param  int|null  $duration  Typing indicator duration in seconds
     * @param  string|null  $url  Media URL (for image/document)
     * @param  string|null  $filename  Filename for media file
     */
    public function sendWhatsAppMessage(
        string $phoneNumber,
        string $message,
        string $deviceToken,
        ?string $inboxId = null,
        ?int $duration = null,
        ?string $url = null,
        ?string $filename = null
    ): array {
        $params = [
            'target' => $phoneNumber,
            'message' => $message,
        ];

        // Add optional parameters
        if ($inboxId) {
            $params['inboxid'] = $inboxId;
        }

        if ($duration) {
            $params['duration'] = $duration;
        }

        if ($url) {
            $params['url'] = $url;
        }

        if ($filename) {
            $params['filename'] = $filename;
        }

        return $this->makeRequest(self::ENDPOINTS['send_message'], $params, false, $deviceToken);
    }

    public function getAllDevices(): array
    {
        return $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);
    }

    /**
     * @return mixed[]
     */
    public function addDevice($name, $phoneNumber): array
    {
        $params = [
            'name' => $name,
            'device' => $phoneNumber,
            'autoread' => false,   // pakai boolean
            'personal' => true,
            'group' => false,
        ];

        // Log request untuk memastikan payload benar
        Log::info('Fonnte Add Device Request', ['params' => $params]);

        // Kirim request
        $response = $this->makeRequest(self::ENDPOINTS['add_device'], $params, true);

        // Cek dan log respons API
        if (! $response['status'] || empty($response['data']['status'])) {
            Log::error('Failed to add device', ['response' => $response]);

            return [
                'status' => false,
                'error' => $response['data']['reason'] ?? 'Invalid or empty body value',
            ];
        }

        return [
            'status' => true,
            'data' => $response['data'],
        ];
    }

    public function requestQRActivation($phoneNumber, $deviceToken): array
    {
        // Kirim permintaan untuk mengaktifkan akun baru dengan QR code
        $response = Http::withHeaders([
            'Authorization' => $deviceToken, // Gunakan account_token dari properti
        ])->post(self::ENDPOINTS['qr_activation'], [
            'type' => 'qr',
            'whatsapp' => $phoneNumber, // Nomor WhatsApp yang diaktivasi
        ]);

        // Periksa jika respons gagal dan ambil pesan error dari respons API
        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->body() ?? 'Unknown error occurred',
            ];
        }

        // Jika berhasil, kembalikan data respons
        return [
            'status' => true,
            'data' => $response->json(), // Kembalikan seluruh data respons
        ];
    }

    public function getDeviceProfile($deviceToken): array
    {
        return $this->makeRequest(self::ENDPOINTS['device_profile'], [], false, $deviceToken);
    }

    public function disconnectDevice($deviceToken): array
    {
        return $this->makeRequest(self::ENDPOINTS['disconnect'], [], false, $deviceToken);
    }

    // Method untuk request OTP menggunakan token perangkat
    public function requestOTPForDeleteDevice($deviceToken): array
    {
        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => ''], false, $deviceToken);
    }

    public function submitOTPForDeleteDevice($otp, $deviceToken): array
    {
        Log::info('Menghapus perangkat dengan OTP', ['otp' => $otp, 'device_token' => $deviceToken]);

        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => (int) $otp], false, $deviceToken);
    }

    public function getDeviceStatus($phoneNumber): array
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.account_token'), // Ensure you're using the correct token
        ])->get(self::ENDPOINTS['check_device_status'], [
            'whatsapp' => $phoneNumber,
        ]);

        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->body() ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $response->json(),
        ];
    }

    /**
     * NEW: Send typing indicator to target number
     * Shows typing indicator during long webhook processes
     *
     * @param  string  $phoneNumber  Target phone number
     * @param  string  $deviceToken  Device token for authentication
     */
    public function sendTyping(string $phoneNumber, string $deviceToken): array
    {
        return $this->makeRequest(self::ENDPOINTS['typing'], [
            'target' => $phoneNumber,
        ], false, $deviceToken);
    }

    /**
     * NEW: Reschedule a message
     *
     * @param  string  $messageId  Message ID to reschedule
     * @param  string  $schedule  New schedule time (format: Y-m-d H:i:s)
     * @param  string  $deviceToken  Device token for authentication
     */
    public function rescheduleMessage(string $messageId, string $schedule, string $deviceToken): array
    {
        return $this->makeRequest(self::ENDPOINTS['reschedule'], [
            'id' => $messageId,
            'schedule' => $schedule,
        ], false, $deviceToken);
    }

    /**
     * NEW: Get statistics for outgoing and incoming messages
     *
     * @param  string  $deviceToken  Device token for authentication
     * @param  string|null  $startDate  Start date (format: Y-m-d)
     * @param  string|null  $endDate  End date (format: Y-m-d)
     */
    public function getStatistics(string $deviceToken, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = [];
        if ($startDate) {
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $params['end'] = $endDate;
        }

        return $this->makeRequest(self::ENDPOINTS['statistics'], $params, false, $deviceToken);
    }

    /**
     * NEW: Send media message (image/document)
     *
     * @param  string  $phoneNumber  Target phone number
     * @param  string  $message  Caption/message
     * @param  string  $deviceToken  Device token for authentication
     * @param  string  $mediaUrl  URL of the media file
     * @param  string|null  $filename  Filename for the media
     * @param  string|null  $inboxId  For reply-to-message feature
     */
    public function sendMediaMessage(
        string $phoneNumber,
        string $message,
        string $deviceToken,
        string $mediaUrl,
        ?string $filename = null,
        ?string $inboxId = null
    ): array {
        return $this->sendWhatsAppMessage(
            $phoneNumber,
            $message,
            $deviceToken,
            $inboxId,
            null,
            $mediaUrl,
            $filename
        );
    }

    /**
     * Convenience method to send image message
     *
     * @param  string  $phoneNumber  Target phone number
     * @param  string  $caption  Image caption
     * @param  string  $imageUrl  URL of the image
     * @param  string  $deviceToken  Device token for authentication
     */
    public function sendImage(string $phoneNumber, string $caption, string $imageUrl, string $deviceToken): array
    {
        return $this->sendMediaMessage($phoneNumber, $caption, $deviceToken, $imageUrl);
    }

    /**
     * Convenience method to send document message
     *
     * @param  string  $phoneNumber  Target phone number
     * @param  string  $caption  Document caption
     * @param  string  $docUrl  URL of the document
     * @param  string  $filename  Filename for the document
     * @param  string  $deviceToken  Device token for authentication
     */
    public function sendDocument(string $phoneNumber, string $caption, string $docUrl, string $filename, string $deviceToken): array
    {
        return $this->sendMediaMessage($phoneNumber, $caption, $deviceToken, $docUrl, $filename);
    }
}
