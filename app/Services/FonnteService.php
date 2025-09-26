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

    ];

    public function __construct()
    {
        $this->account_token = env('ACCOUNT_TOKEN');
    }

    protected function makeRequest($endpoint, $params = [], $useAccountToken = true, $deviceToken = null)
    {
        $token = $useAccountToken
            ? $this->account_token
            : ($deviceToken ?? null);

        if (! $token) {
            return ['status' => false, 'error' => 'API token or device token is required.'];
        }

        // Gunakan JSON format dan pastikan Content-Type header benar
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json', // Tambahkan header
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

    public function sendWhatsAppMessage($phoneNumber, $message, $deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['send_message'], [
            'target' => $phoneNumber,
            'message' => $message,
        ], false, $deviceToken);
    }

    public function getAllDevices()
    {
        return $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);
    }

    public function addDevice($name, $phoneNumber)
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

    public function requestQRActivation($phoneNumber, $deviceToken)
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

    public function getDeviceProfile($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['device_profile'], [], false, $deviceToken);
    }

    public function disconnectDevice($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['disconnect'], [], false, $deviceToken);
    }

    // Method untuk request OTP menggunakan token perangkat
    public function requestOTPForDeleteDevice($deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => ''], false, $deviceToken);
    }

    public function submitOTPForDeleteDevice($otp, $deviceToken)
    {
        Log::info('Menghapus perangkat dengan OTP', ['otp' => $otp, 'device_token' => $deviceToken]);

        return $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => (int) $otp], false, $deviceToken);
    }

    public function getDeviceStatus($phoneNumber)
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
}
