<?php

use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use function Livewire\Volt\{state, mount, action};
use Filament\Notifications\Notification;

state([
    'devices' => [],
    'name' => '',
    'device' => '',
    'otp' => '',
    'qrUrl' => null,
    'selectedToken' => null,
    'selectedDevice' => null,
]);

// ambil semua device saat mount
mount(function () {
    $fonnteService = app(FonnteService::class);
    $this->reloadDevices($fonnteService);
});

// helper reload devices
$reloadDevices = action(function () {
    $fonnteService = app(FonnteService::class);
    try {
        $res = $fonnteService->getAllDevices();
        if (data_get($res, 'status')) {
            $api = data_get($res, 'data', []);
            $this->devices = data_get($api, 'data', $api);
        } else {
            $this->devices = [];
            $error = data_get($res, 'error', 'Gagal memanggil API');
            Notification::make()->title(title: 'Gagal memanggil API')->body($error)->danger()->send();
        }
    } catch (\Throwable $e) {
        Log::error('Error reload devices: ' . $e->getMessage());
        $this->devices = [];

        $error = $e->getMessage();
        Notification::make()->title(title: 'Gagal Reload Device')->body($error)->danger()->send();
    }
});

// disconnect
$disconnectDevice = action(function ($token) {
    $fonnteService = app(FonnteService::class);
    try {
        $res = $fonnteService->disconnectDevice($token);
        if (!data_get($res, 'status')) {
            $reason = data_get($res, 'error', 'Gagal disconnect device');
            $detail = data_get($res, 'data.detail') ?? 'Periksa koneksi internet atau coba lagi nanti';

            Notification::make()
                ->title('❌ Gagal Disconnect Device')
                ->body("Error: {$reason}\nDetail: {$detail}")
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()->title('✅ Device Berhasil Disconnect')->body('Device telah terputus dari WhatsApp. Webhook tidak akan menerima pesan lagi.')->success()->send();

        $this->reloadDevices($fonnteService);
    } catch (\Exception $e) {
        Log::error('Disconnect device error: ' . $e->getMessage());
        Notification::make()
            ->title('❌ Error Sistem')
            ->body('Terjadi kesalahan internal: ' . $e->getMessage())
            ->danger()
            ->send();
    }
});

$requestDeleteOtp = action(function (string $deviceToken, ?string $otp = null) {
    $fonnteService = app(FonnteService::class);
    $this->selectedToken = $deviceToken;

    try {
        $res = $fonnteService->requestOTPForDeleteDevice($deviceToken);

        if (!data_get($res, 'status')) {
            $reason = data_get($res, 'error') ?? (data_get($res, 'data.reason') ?? 'Gagal mengirim OTP.');
            Notification::make()
                ->title('Gagal mengirim OTP')
                ->body($reason) // tampilkan detail dari API
                ->danger()
                ->send();
            return;
        }

        Notification::make()->title('OTP sudah dikirim, silakan masukkan OTP untuk menghapus device.')->success()->send();

        $this->dispatch('open-modal', id: 'requestDeleteOtp');
        $this->otp = '';
    } catch (\Throwable $e) {
        Log::error('requestDeleteOtp: ' . $e->getMessage());
        $error = 'Internal error: ' . $e->getMessage();
        Notification::make()->title('Internal error')->body($error)->danger()->send();
    }
});

// Submit OTP untuk hapus device
$confirmDelete = action(function () {
    $fonnteService = app(FonnteService::class);
    if (!$this->selectedToken) {
        Notification::make()->title('Token tidak ditemukan.')->danger()->send();

        return;
    }

    try {
        $res = $fonnteService->submitOTPForDeleteDevice($this->otp, $this->selectedToken);

        if (!data_get($res, 'status')) {
            $reason = data_get($res, 'error') ?? (data_get($res, 'data.reason') ?? 'OTP salah atau gagal hapus.');
            Notification::make()->title('Gagal hapus device')->body($reason)->danger()->send();
            return;
        }

        // Hapus dari database lokal
        $device = Device::where('token', $this->selectedToken)->first();
        if ($device) {
            $device->delete();
        }

        $detail = data_get($res, 'data.detail') ?? 'Device berhasil dihapus!';
        Notification::make()->title('Berhasil')->body($detail)->success()->send();

        $this->dispatch('close-modal', id: 'requestDeleteOtp');

        $this->otp = '';

        // reload devices (kalau ada fungsi reload)
        $this->reloadDevices($fonnteService);
    } catch (\Throwable $e) {
        Log::error('confirmDelete: ' . $e->getMessage());
        $error = 'Internal error: ' . $e->getMessage();

        Notification::make()->title('Internal error')->body($error)->danger()->send();
        return;
    }
});

$activateDevice = action(function (string $device, string $token) {
    $fonnteService = app(FonnteService::class);
    try {
        // Cek status device dulu
        $profileRes = $fonnteService->getDeviceProfile($token);
        if (data_get($profileRes, 'data.device_status') === 'connect') {
            Notification::make()->title('ℹ️ Device Sudah Terhubung')->body('Device ini sudah connected. Tidak perlu connect ulang.')->info()->send();
            $this->reloadDevices($fonnteService);
            return;
        }

        // Request QR activation
        $res = $fonnteService->requestQRActivation($device, $token);

        if (!data_get($res, 'status')) {
            $reason = data_get($res, 'error') ?? data_get($res, 'reason', 'Gagal generate QR Code');
            $detail = data_get($res, 'data.detail') ?? 'Periksa koneksi internet atau coba lagi nanti';

            // Special handling for specific errors
            if (str_contains(strtolower($reason), 'already connect')) {
                Notification::make()->title('ℹ️ Device Sudah Connected')->body('Device sudah terhubung sebelumnya. Status akan diperbarui.')->info()->send();
                $this->reloadDevices($fonnteService);
                return;
            }

            Notification::make()
                ->title('❌ Gagal Generate QR Code')
                ->body("Error: {$reason}\nDetail: {$detail}")
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        $this->qrUrl = data_get($res, 'data.url');
        $this->selectedToken = $token;
        $this->selectedDevice = $device;

        if (!$this->qrUrl) {
            Notification::make()->title('❌ QR Code Tidak Ditemukan')->body('Fonnte tidak mengembalikan URL QR code. Coba lagi atau hubungi support.')->danger()->send();
            return;
        }

        // Buka modal connect
        $this->dispatch('open-modal', id: 'connectDevice');

        Notification::make()->title('📱 QR Code Generated')->body('Silakan scan QR code di modal yang muncul dengan WhatsApp Anda.')->info()->send();

        // Mulai polling status
        $this->dispatch('start-device-polling');
    } catch (\Throwable $e) {
        Log::error('activateDevice error: ' . $e->getMessage());
        Notification::make()
            ->title('❌ Error Sistem')
            ->body('Terjadi kesalahan internal saat menghubungkan device: ' . $e->getMessage())
            ->danger()
            ->send();
    }
});

$checkDeviceStatus = action(function () {
    $fonnteService = app(FonnteService::class);
    if (!$this->selectedToken) {
        Notification::make()->title('❌ Tidak Ada Device Dipilih')->body('Token device tidak ditemukan. Coba refresh halaman.')->danger()->send();
        return;
    }

    try {
        $res = $fonnteService->getDeviceProfile($this->selectedToken);

        if (!data_get($res, 'status')) {
            $error = data_get($res, 'error', 'Gagal mendapatkan status device');
            Notification::make()
                ->title('❌ Gagal Cek Status')
                ->body("Error: {$error}")
                ->danger()
                ->send();
            return;
        }

        $deviceStatus = data_get($res, 'data.device_status');
        $deviceName = data_get($res, 'data.name', 'Unknown Device');

        if ($deviceStatus === 'connect') {
            // Success - close modal and redirect
            $this->dispatch('close-modal', id: 'connectDevice');

            Notification::make()
                ->title('🎉 Device Berhasil Terhubung!')
                ->body("Device '{$deviceName}' sekarang aktif dan siap menerima pesan WhatsApp.")
                ->success()
                ->persistent()
                ->send();

            // Reload devices to update status
            $this->reloadDevices($fonnteService);

            // Redirect to device list
            return $this->redirectRoute('filament.admin.resources.devices.index');
        } elseif ($deviceStatus === 'disconnect') {
            Notification::make()->title('⏳ Masih Menunggu...')->body('Device belum terhubung. Pastikan Anda sudah scan QR code dengan WhatsApp.')->warning()->send();
        } elseif ($deviceStatus === 'connecting') {
            Notification::make()->title('🔄 Sedang Menghubungkan...')->body('Device sedang dalam proses koneksi. Tunggu beberapa detik lagi.')->info()->send();
        } else {
            Notification::make()
                ->title('❓ Status Tidak Dikenal')
                ->body("Status device: {$deviceStatus}. Coba refresh atau hubungi support.")
                ->warning()
                ->send();
        }
    } catch (\Throwable $e) {
        Log::error('checkDeviceStatus error: ' . $e->getMessage());
        Notification::make()
            ->title('❌ Error Sistem')
            ->body('Terjadi kesalahan saat mengecek status device: ' . $e->getMessage())
            ->danger()
            ->send();
    }
});

$copyToClipboard = action(function (string $token) {
    $fonnteService = app(FonnteService::class);
    // Validate token using FonnteService
    $res = $fonnteService->getDeviceProfile($token);
    if (!data_get($res, 'status')) {
        Notification::make()->title('Token tidak valid')->body(data_get($res, 'error', 'Gagal memvalidasi token'))->danger()->send();
        return;
    }

    // Dispatch event to copy token to clipboard via JavaScript
    $this->dispatch('copy-to-clipboard', token: $token);
    Notification::make()->title('Token berhasil disalin ke clipboard')->success()->send();
});

$testWebhook = action(function () {
    $fonnteService = app(FonnteService::class);
    $webhookUrl = env('NGROK_WEBHOOK_URL');

    if (!$webhookUrl) {
        Notification::make()->title('❌ Webhook URL Tidak Dikonfigurasi')->body('Set NGROK_WEBHOOK_URL di file .env terlebih dahulu.')->danger()->send();
        return;
    }

    $testPayload = [
        'sender' => '6285951572182',
        'message' => 'test webhook',
        'device' => '6285951572182',
        'type' => 'text',
    ];

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)->post($webhookUrl . '/webhook/whatsapp', $testPayload);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['status'] ?? null) === 'ok') {
                Notification::make()->title('✅ Webhook Berfungsi!')->body('Webhook berhasil menerima dan memproses test message.')->success()->send();
            } else {
                Notification::make()
                    ->title('⚠️ Webhook Response Tidak Normal')
                    ->body('Response: ' . json_encode($data))
                    ->warning()
                    ->send();
            }
        } else {
            Notification::make()
                ->title('❌ Webhook Error')
                ->body("HTTP {$response->status()}: {$response->body()}")
                ->danger()
                ->send();
        }
    } catch (\Exception $e) {
        Notification::make()
            ->title('❌ Test Webhook Gagal')
            ->body('Error: ' . $e->getMessage())
            ->danger()
            ->send();
    }
});

?>

<x-filament-panels::page>

    @volt
        <div>
            {{-- Status Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Device Terhubung -->
                <div class="flex items-center p-4 bg-green-100 border border-green-200 rounded-xl shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600 mr-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 17h10M9 21h6m-7-4V5a2 2 0 012-2h4a2 2 0 012 2v12H8z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Device Terhubung</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ count(collect($devices)->where('status', 'connect')) }}
                        </p>
                    </div>
                </div>

                <!-- Device Terputus -->
                <div class="flex items-center p-4 bg-red-100 border border-red-200 rounded-xl shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600 mr-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M5.07 19h13.86c.89 0 1.34-1.08.71-1.71L12.71 4.29a1 1 0 00-1.42 0L4.36 17.29c-.63.63-.18 1.71.71 1.71z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Device Terputus</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ count(collect($devices)->where('status', 'disconnect')) }}
                        </p>
                    </div>
                </div>

                <!-- Total Device -->
                <div class="flex items-center p-4 bg-blue-100 border border-blue-200 rounded-xl shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-600 mr-3" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Device</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ count($devices) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Connection Guide --}}
            @if (count(collect($devices)->where('status', 'disconnect')) > 0)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-orange-500" />
                            <span>Panduan Koneksi Device</span>
                        </div>
                    </x-slot>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex-shrink-0 w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <div>
                                <p class="font-medium">Buka Fonnte Dashboard</p>
                                <p class="text-gray-600">Kunjungi <a href="https://fonnte.com/device" target="_blank"
                                        class="text-blue-600 underline">https://fonnte.com/device</a></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span
                                class="flex-shrink-0 w-6 h-6 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <div>
                                <p class="font-medium">Cari Device yang Terputus</p>
                                <p class="text-gray-600">Temukan device dengan status "disconnect" di dashboard Fonnte</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span
                                class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <div>
                                <p class="font-medium">Klik "Connect" dan Scan QR</p>
                                <p class="text-gray-600">Buka WhatsApp di HP Anda dan scan QR code yang muncul</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span
                                class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <div>
                                <p class="font-medium">Test Koneksi</p>
                                <p class="text-gray-600">Kirim pesan "menu" ke nomor device untuk test bot WhatsApp</p>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @endif

            {{-- Webhook Info --}}
            <x-filament::section icon="heroicon-o-globe-alt" >
                <x-slot name="heading">
                    Webhook Configuration
                </x-slot>
                <x-slot name="description">
                    Webhook menerima pesan WhatsApp secara real-time.
                    Pastikan URL di atas sudah diset di Fonnte Dashboard → Webhook Settings.
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Webhook URL</p>
                        <p class="text-sm text-gray-600 break-all">
                            {{ env('NGROK_WEBHOOK_URL') ? env('NGROK_WEBHOOK_URL') . '/webhook/whatsapp' : 'Belum dikonfigurasi' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Status</p>
                        <div
                            class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            {{ count(collect($devices)->where('status', 'connect')) > 0 ? 'Aktif' : 'Menunggu Device Connect' }}
                        </div>
                    </div>
                </div>

                {{-- Test Webhook Button --}}
                <div class="mt-4 flex gap-3">
                    <x-filament::button wire:click="$dispatch('test-webhook')" size="sm" color="gray" outlined>
                        <x-heroicon-o-paper-airplane class="w-4 h-4 mr-2" />
                        Test Webhook
                    </x-filament::button>

                    @if (env('NGROK_WEBHOOK_URL'))
                        <a href="{{ env('NGROK_WEBHOOK_URL') }}/webhook/whatsapp"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <x-heroicon-o-globe-alt class="w-4 h-4 mr-2" />
                            Open Webhook URL
                        </a>
                    @endif
                </div>
                <div class="relative flex flex-col w-full h-full overflow-x-auto overflow-visible min-h-screen ">
                    <table class="w-full text-left table-auto min-w-max">
                        <thead>
                            <tr>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        #
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Name
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Phone
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Qouta
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Status Koneksi
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Webhook
                                    </p>
                                </th>
                                <th class="p-4 border-b border-blue-gray-100 bg-blue-gray-50">
                                    <p
                                        class="block font-sans text-sm antialiased font-normal leading-none text-blue-gray-900 opacity-70">
                                        Action
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $index => $device)
                                <tr>
                                    <td class="p-4 border-y">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ ++$index }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-y">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['name'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-y">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['device'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-y">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['quota'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-y">
                                        <div class="flex flex-col gap-1">
                                            <x-filament::badge :color="$device['status'] === 'connect' ? 'success' : 'danger'">
                                                {{ $device['status'] === 'connect' ? 'Terhubung' : 'Terputus' }}
                                            </x-filament::badge>
                                        </div>
                                    </td>

                                    <td class="p-4 border-y">
                                        <div class="flex flex-col gap-1">
                                            @if ($device['status'] === 'connect')
                                                <x-filament::badge color="success">
                                                    Aktif
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="warning">
                                                    Non-aktif
                                                </x-filament::badge>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-4 border-y">
                                        <x-filament::dropdown placement="bottom-end" class="relative">
                                            <x-slot name="trigger">
                                                <x-filament::button icon="heroicon-m-ellipsis-vertical" size="xs"
                                                    outlined>

                                                </x-filament::button>
                                            </x-slot>

                                            <x-filament::dropdown.list class="z-[9999]">
                                                <x-filament::dropdown.list.item
                                                    wire:click="copyToClipboard('{{ $device['token'] }}')">
                                                    Copy Token
                                                </x-filament::dropdown.list.item>
                                                @if ($device['status'] === 'connect')
                                                    <x-filament::dropdown.list.item
                                                        wire:click="disconnectDevice('{{ data_get($device, 'token') ?? '' }}')"
                                                        class="disconnectButton"
                                                        data-device-token="{{ $device['token'] }}">
                                                        Disconnect
                                                    </x-filament::dropdown.list.item>
                                                @else
                                                    <x-filament::dropdown.list.item
                                                        wire:click="activateDevice('{{ $device['device'] }}', '{{ $device['token'] }}')">
                                                        Connect
                                                    </x-filament::dropdown.list.item>
                                                @endif
                                                <x-filament::dropdown.list.item
                                                    wire:click="requestDeleteOtp('{{ $device['token'] }}')"
                                                    size="xs">
                                                    Delete
                                                </x-filament::dropdown.list.item>
                                            </x-filament::dropdown.list>
                                        </x-filament::dropdown>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- modal request Delete Otp --}}
            @include('filament.resources.devices.partials.modal-request-delete-otp')

            {{-- modal connect --}}
            @include('filament.resources.devices.partials.modal-connect')

        </div>
    @endvolt

    <style>
        /* Fix dropdown positioning to prevent being cut off by header */
        [x-data*="dropdown"] {
            position: relative !important;
        }

        /* Ensure dropdown menu has proper z-index and positioning */
        [role="menu"] {
            z-index: 9999 !important;
            position: absolute !important;
        }

        /* Fix table overflow to allow dropdown to show */
        .fi-section-content-ctn {
            overflow: visible !important;
        }

        table tbody {
            position: relative;
            z-index: 1;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('copy-to-clipboard', (data) => {
                navigator.clipboard.writeText(data.token).then(() => {
                    console.log('Token copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy token: ', err);
                });
            });

            // Handle test webhook event
            Livewire.on('test-webhook', () => {
                $wire.call('testWebhook');
            });
        });
    </script>

</x-filament-panels::page>
