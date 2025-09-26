<?php

use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use function Livewire\Volt\{state, mount, action};
use Filament\Notifications\Notification;

$fonnteService = app(FonnteService::class);

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
mount(function () use ($fonnteService) {
    $this->reloadDevices($fonnteService);
});

// helper reload devices
$reloadDevices = action(function ($fonnteService) {
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
$disconnectDevice = action(function ($token) use ($fonnteService) {
    $res = $fonnteService->disconnectDevice($token);
    if (!data_get($res, 'status')) {
        $reason = data_get($res, 'error', 'Gagal disconnect');
        Notification::make()->title('Gagal disconnect')->body($reason)->danger()->send();

        return;
    }
    Notification::make()->title('Device berhasil disconnect')->success()->send();

    $this->reloadDevices($fonnteService);
});

$requestDeleteOtp = action(function (string $deviceToken, ?string $otp = null) use ($fonnteService) {
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
$confirmDelete = action(function () use ($fonnteService) {
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

$activateDevice = action(function (string $device, string $token) use ($fonnteService) {
    try {
        // pakai API Get QR
        $res = $fonnteService->requestQRActivation($device, $token);
        // atau bisa custom method untuk endpoint qr, tergantung implementasi

        if (!data_get($res, 'status')) {
            $reason = data_get($res, 'error') ?? data_get($res, 'reason', 'Gagal generate QR Code');
            Notification::make()->title('Gagal Connect')->body($reason)->danger()->send();
            // kalau reason = "device already connect", mungkin bisa langsung reload dan notif sukses
            if ($reason === 'device already connect') {
                $this->reloadDevices($fonnteService);
                Notification::make()->title('Device sudah terhubung')->success()->send();
            }
            return;
        }

        $this->qrUrl = data_get($res, 'data.url');
        $this->selectedToken = $token;
        $this->selectedDevice = $device;

        // buka modal
        $this->dispatch('open-modal', id: 'connectDevice');

        // mulai polling status
        $this->dispatch('start-device-polling');
    } catch (\Throwable $e) {
        Log::error('activateDevice error: ' . $e->getMessage());
        Notification::make()->title('Internal error')->body($e->getMessage())->danger()->send();
    }
});

$checkDeviceStatus = action(function () use ($fonnteService) {
    // Cek status saat ini
    if (!$this->selectedToken) {
        Notification::make()->title('Tidak ada device dipilih')->danger()->send();
        return;
    }

    try {
        $res = $fonnteService->getDeviceProfile($this->selectedToken);

        // dd($res);

        if (data_get($res, 'data.device_status') === 'connect') {
            // sukses, tutup modal
            $this->dispatch('close-modal', id: 'connectDevice');

            // notif
            Notification::make()->title('Device Connected!')->body('WhatsApp berhasil terhubung.')->success()->send();

            // redirect ke route filament.admin.resources.devices.index
            return $this->redirectRoute('filament.admin.resources.devices.index');
        } else {
            Notification::make()->title('Belum terkoneksi')->body('Device belum berhasil connect. Silakan scan QR dan coba lagi.')->warning()->send();
        }
    } catch (\Throwable $e) {
        Log::error('confirmConnect error: ' . $e->getMessage());
        Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
    }
});

$copyToClipboard = action(function (string $token) use ($fonnteService) {
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

?>

<x-filament-panels::page>

    @volt
        <div>
            <x-filament::section>
                <div class="relative flex flex-col w-full h-full overflow-scroll">
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
                                        Status
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
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ ++$index }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['name'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['device'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <p
                                            class="block font-sans text-sm antialiased font-normal leading-normal text-blue-gray-900">
                                            {{ $device['quota'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <x-filament::badge class="capitalize">
                                            {{ $device['status'] }}
                                        </x-filament::badge>

                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50">
                                        <x-filament::dropdown>
                                            <x-slot name="trigger">
                                                <x-filament::button icon="heroicon-m-ellipsis-vertical" size="xs"
                                                    outlined>

                                                </x-filament::button>
                                            </x-slot>

                                            <x-filament::dropdown.list>
                                                <x-filament::dropdown.list.item
                                                    wire:click="copyToClipboard('{{ $device['token'] }}')">
                                                    Copy Token
                                                </x-filament::dropdown.list.item>
                                                @if ($device['status'] === 'connect')
                                                    <x-filament::dropdown.list.item
                                                        wire:click="disconnectDevice('{{ data_get($device, 'token') ?? '' }}')"
                                                        class="disconnectButton" data-device-token="{{ $device['token'] }}">
                                                        Disconnect
                                                    </x-filament::dropdown.list.item>
                                                @else
                                                    <x-filament::dropdown.list.item
                                                        wire:click="activateDevice('{{ $device['device'] }}', '{{ $device['token'] }}')">
                                                        Connect
                                                    </x-filament::dropdown.list.item>
                                                @endif
                                                <x-filament::dropdown.list.item
                                                    wire:click="requestDeleteOtp('{{ $device['token'] }}')" size="xs">
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('copy-to-clipboard', (data) => {
                navigator.clipboard.writeText(data.token).then(() => {
                    console.log('Token copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy token: ', err);
                });
            });
        });
    </script>

</x-filament-panels::page>
