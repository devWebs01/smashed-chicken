<?php

use function Livewire\Volt\{state, rules};

use App\Models\Order;

state([
    'customer_name' => '',
    'customer_phone' => '',
    'customer_address' => '',
    'delivery_method' => 'dine_in',
]);

rules([
    'customer_name' => 'nullable|string|max:255',
    'customer_phone' => 'nullable|string|max:20',
    'customer_address' => 'nullable|string',
    'delivery_method' => 'required|string|in:dine_in,takeaway,delivery',
]);

$createOrder = function () {
    $validated_data = $this->validate();

    $record = Order::create(
        array_merge($validated_data, [
            'status' => 'draft',
            'total_price' => 0,
            'order_date_time' => now(),
            'payment_method' => '',
        ]),
    );

    return redirect()->route('filament.admin.resources.orders.items', ['record' => $record->id]);
};

?>

<x-filament-panels::page>
    @volt
        <div>
            <x-filament::section>

                <form wire:submit.prevent="createOrder" class="space-y-6">
                    {{-- Nama Customer --}}
                    <div>
                        <p class="mb-2 font-medium text-sm">Nama Customer</p>
                        <x-filament::input.wrapper :valid="!$errors->has('customer_name')">
                            <x-filament::input id="customer_name" type="text" wire:model.defer="customer_name"
                                placeholder="Masukkan nama customer" />
                        </x-filament::input.wrapper>

                    </div>

                    {{-- Telepon --}}
                    <div>
                        <p class="mb-2 font-medium text-sm">Telepon</p>
                        <x-filament::input.wrapper :valid="!$errors->has('customer_phone')">
                            <x-filament::input id="customer_phone" type="text" wire:model.defer="customer_phone"
                                placeholder="08xxxxxxxxxx" />
                        </x-filament::input.wrapper>

                    </div>

                    {{-- Metode Pengantaran --}}
                    <div>
                        <p class="mb-2 font-medium text-sm">Metode Pengantaran</p>
                        <x-filament::input.wrapper :valid="!$errors->has('delivery_method')">
                            <x-filament::input.select id="delivery_method" wire:model.defer="delivery_method">
                                <option value="dine_in">Makan di Tempat</option>
                                <option value="takeaway">Bawa Pulang</option>
                                <option value="delivery">Delivery</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex gap-3 justify-end">
                        <x-filament::button color="gray" type="button" wire:click="$refresh">
                            Batal
                        </x-filament::button>
                        <x-filament::button color="primary" type="submit">
                            Lanjutkan ke Menu
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>
    @endvolt
</x-filament-panels::page>
