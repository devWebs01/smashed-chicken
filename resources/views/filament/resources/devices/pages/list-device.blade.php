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
    'loading' => false,
    'error' => '',
    'success' => '',
    'otp' => '',
    'selectedToken' => null,
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
                                        <p
                                            class="block font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
                                            {{ $device['status'] }}
                                        </p>
                                    </td>
                                    <td class="p-4 border-b border-blue-gray-50 space-y-3">
                                        <x-filament::button size="xs" color="info"
                                            wire:click="copyToClipboard('{{ $device['token'] }}')">
                                            Copy Token
                                        </x-filament::button>

                                        @if ($device['status'] === 'connect')
                                            <x-filament::button size="xs" color="secondary"
                                                wire:click="openSendMessageModal('{{ $device['token'] }}')">
                                                Send Message
                                            </x-filament::button>

                                            <x-filament::button size="xs" color="danger"
                                                wire:click="disconnectDevice('{{ data_get($device, 'token') ?? '' }}')"
                                                class="disconnectButton" data-device-token="{{ $device['token'] }}">
                                                Disconnect
                                            </x-filament::button>
                                        @else
                                            <x-filament::button size="xs" color="success"
                                                wire:click="activateDevice('{{ data_get($device, 'device') }}', '{{ data_get($device, 'token') ?? '' }}')"
                                                class="connectButton">
                                                Connect
                                            </x-filament::button>
                                        @endif

                                        <!-- Tombol Delete (minta OTP) -->
                                        <x-filament::button color="danger"
                                            wire:click="requestDeleteOtp('{{ $device['token'] }}')" size="xs">
                                            Delete
                                        </x-filament::button>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::modal icon="heroicon-o-exclamation-triangle" icon-color="danger" id="requestDeleteOtp">
                <x-slot name="heading">
                    Konfirmasi Hapus Device
                </x-slot>
                <x-slot name="description">
                    Masukkan OTP yang sudah dikirim ke WhatsApp untuk menghapus device ini.
                </x-slot>

                <x-filament::input wire:model="otp" placeholder="Masukkan OTP" />

                <x-slot name="footerActions">
                    <x-filament::button color="secondary" wire:click="$dispatch('close-modal', { id: 'requestDeleteOtp' })">
                        Batal
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="confirmDelete">
                        Hapus
                    </x-filament::button>
                </x-slot>

            </x-filament::modal>

        </div>
    @endvolt
</x-filament-panels::page>
