<?php

use function Livewire\Volt\{state, computed, usesPagination};
use App\Models\{Product, Order, OrderItem};
use Illuminate\Support\Facades\DB;

usesPagination();

state([
    'record' => fn() => request()->route('record'),
    'currentOrder' => collect([]),
]);

state(['search'])->url();

$menuItems = computed(function () {
    $query = Product::query();

    if ($this->search) {
        $query->where('name', 'like', '%' . $this->search . '%');
    }

    return $query->simplePaginate(5);
});

$updatingSearch = function () {
    $this->resetPage();
};

$addItem = function ($productId) {
    $product = Product::find($productId);

    $existing = $this->currentOrder->firstWhere('id', $product->id);

    if ($existing) {
        $this->incrementQuantity($product->id);
    } else {
        $this->currentOrder->push(
            (object) [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ],
        );
    }
};

$incrementQuantity = function ($productId) {
    $this->currentOrder = $this->currentOrder->map(function ($item) use ($productId) {
        if ($item->id === $productId) {
            $item->quantity++;
        }
        return $item;
    });
};

$decrementQuantity = function ($productId) {
    $this->currentOrder = $this->currentOrder
        ->map(function ($item) use ($productId) {
            if ($item->id === $productId) {
                $item->quantity = max(0, ((int) $item->quantity) - 1);
            }
            return $item;
        })
        ->filter(fn($item) => $item->quantity > 0)
        ->values();
};

$totalAmount = computed(function () {
    return $this->currentOrder->sum(fn($item) => $item->price * $item->quantity);
});

$saveDraft = function () {
    DB::transaction(function () {
        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => 'draft',
            'total_price' => $this->totalAmount,
        ]);

        foreach ($this->currentOrder as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->price * $item->quantity,
            ]);
        }

        return redirect()->route('filament.admin.resources.orders.show', $order);
    });
};

$printBill = function () {
    return redirect()->route('orders.print', $this->record);
};

$openOrder = function () {
    DB::transaction(function () {
        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => 'open',
            'total_price' => $this->totalAmount,
        ]);

        foreach ($this->currentOrder as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->price * $item->quantity,
            ]);
        }

        return redirect()->route('filament.admin.resources.orders.show', $order);
    });
};

?>

<x-filament-panels::page>
    @volt
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Table {{ $record }}</h2>
                        <x-filament::button color="gray" size="sm">
                            New Order
                        </x-filament::button>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Panel Kiri (Menu Items) --}}
                    <x-filament::section class="space-y-4">
                        {{-- Search Bar --}}
                        <x-filament::input.wrapper class="mb-4">
                            <x-filament::input wire:model.live="search" type="search" placeholder="Cari menu..." />
                        </x-filament::input.wrapper>

                        {{-- Daftar Menu --}}
                        <div class="space-y-3 max-h-[65vh] overflow-y-auto hide-scrollbar pr-2">
                            @forelse ($this->menuItems as $item)
                                <div
                                    class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow flex justify-between items-center hover:ring-2 hover:ring-primary-500 transition">
                                    <div class="flex flex-col">
                                        <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                                            {{ Str::limit($item->name, 40) }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($item->description, 50) }}
                                        </p>
                                        <span class="mt-1 text-sm font-bold text-primary-600">
                                            {{ formatRupiah($item->price) }}
                                        </span>
                                    </div>
                                    <x-filament::icon-button icon="heroicon-m-plus" size="lg" color="primary"
                                        class="rounded-full shadow" wire:click="addItem({{ $item->id }})" />
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada produk tersedia
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            {{ $this->menuItems->links() }}
                        </div>
                    </x-filament::section>

                    {{-- Panel Kanan (Current Order) --}}
                    <x-filament::section
                        class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md flex flex-col justify-between">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold">Pesanan Saat Ini</h3>
                                <p class="text-xs text-gray-500">Meja {{ $record }} • {{ now()->format('M d, Y') }}
                                </p>
                            </div>

                            {{-- List Order --}}
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($currentOrder as $orderItem)
                                    <div class="flex items-center justify-between py-3">
                                        <div class="flex items-center gap-2">
                                            <x-filament::icon-button icon="heroicon-m-minus" size="sm" color="gray"
                                                wire:click="decrementQuantity({{ $orderItem->id }})" />
                                            <span
                                                class="text-base font-bold w-6 text-center">{{ $orderItem->quantity }}</span>
                                            <x-filament::icon-button icon="heroicon-m-plus" size="sm" color="gray"
                                                wire:click="incrementQuantity({{ $orderItem->id }})" />
                                        </div>
                                        <span class="flex-1 mx-2 text-sm font-medium">{{ $orderItem->name }}</span>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                            {{ formatRupiah($orderItem->price * $orderItem->quantity) }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-gray-400 text-sm">
                                        Belum ada item ditambahkan
                                    </div>
                                @endforelse
                            </div>

                            <div class="flex justify-between items-center text-lg font-bold pt-4 border-t">
                                <span>Total:</span>
                                <span class="text-primary-600">{{ formatRupiah($this->totalAmount) }}</span>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="space-y-3 mt-6">
                            <x-filament::button icon="heroicon-o-paper-airplane" color="primary" class="w-full"
                                :disabled="$currentOrder->isEmpty()" wire:click="openOrder({{ $record }})">
                                Kirim ke Dapur
                            </x-filament::button>

                            <div class="flex gap-3">
                                <x-filament::button icon="heroicon-o-archive-box-arrow-down" color="warning" outlined
                                    class="flex-1" wire:click="saveDraft">
                                    Simpan Draft
                                </x-filament::button>

                                <x-filament::button icon="heroicon-o-printer" color="success" outlined class="flex-1"
                                    wire:click="printBill({{ $record }})">
                                    Cetak Tagihan
                                </x-filament::button>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            </x-filament::section>
        </div>
    @endvolt

    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari */
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            /* IE, Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
</x-filament-panels::page>
