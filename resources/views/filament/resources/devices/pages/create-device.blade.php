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
            $this->error = data_get($res, 'error', 'Gagal memanggil API');
        }
    } catch (\Throwable $e) {
        Log::error('Error reload devices: ' . $e->getMessage());
        $this->devices = [];
        $this->error = $e->getMessage();
    }
});

// tambah device
$addDevice = action(function () use ($fonnteService) {
    $this->validate([
        'name' => 'required|string|max:255',
        'device' => 'required|string|max:255',
    ]);

    try {
        $res = $fonnteService->addDevice($this->name, $this->device);
        if (!data_get($res, 'status')) {
            Notification::make()->title('Gagal menambah device')->danger()->send();
            return;
        }

        $token = data_get($res, 'data.token') ?? null;
        Device::create([
            'name' => $this->name,
            'device' => $this->device,
            'token' => $token,
        ]);

        Notification::make()->title('Device berhasil ditambahkan')->success()->send();

        $this->name = '';
        $this->device = '';

        $this->reloadDevices($fonnteService);
    } catch (\Throwable $e) {
        $this->error = $e->getMessage();
        Log::error('Add device: ' . $e->getMessage());
        Notification::make()->title('Error menambah device')->body($e->getMessage())->danger()->send();
    }

    return redirect()->route('filament.admin.resources.devices.index');
});

?>

<x-filament-panels::page>

    @volt
        <div>
            <x-filament::section>

                <form wire:submit="addDevice" class="space-y-6 mb-6">

                    <div>
                        <p class="mb-2 font-medium text-sm">Device Name</p>
                        <x-filament::input.wrapper :valid="!$errors->has('name')">
                            <x-filament::input type="text" wire:model="name" />
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <p class="mb-2 font-medium text-sm">WhatsApp Number</p>
                        <x-filament::input.wrapper :valid="!$errors->has('device')">

                            <x-filament::input type="number" wire:model="device" />
                        </x-filament::input.wrapper>

                    </div>
                    <x-filament::button type="submit">
                        New Device
                    </x-filament::button>
                </form>

            </x-filament::section>

        </div>
    @endvolt
</x-filament-panels::page>
