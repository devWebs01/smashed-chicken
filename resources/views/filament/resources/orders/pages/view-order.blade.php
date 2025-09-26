<?php

use function Livewire\Volt\state;
use App\Models\{Order, Setting};

state([
    'record' => fn() => request()->route('record'),
    'order' => fn() => Order::with(['orderItems'])->findOrFail(request()->route('record')),
    'status' => fn() => $order->status ?? 'draft',
    'setting' => fn() => Setting::first(),
]);
?>

<x-filament-panels::page>
    @volt
        <div>
            <div class="bg-white dark:bg-gray-900 shadow-xl rounded-xl invoice-printable max-w-full">
                <div class="p-6 sm:p-8 lg:p-10">

                    {{-- Header --}}
                    <header
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 mb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4 mb-4 sm:mb-0">
                            <div
                                class="w-14 h-14 bg-primary-600 dark:bg-primary-500 rounded-xl flex items-center justify-center">
                                <x-heroicon-s-bolt class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $setting->name }}</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $setting->address }}</p>
                            </div>
                        </div>
                        <div class="text-right">

                            <span @class([
                                'inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold capitalize mt-2',
                            
                                // colors per status
                                'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-500/10 dark:text-yellow-400 dark:ring-yellow-500/20' =>
                                    $status === 'draft',
                                'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20' =>
                                    $status === 'pending',
                                'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20' => in_array(
                                    $status,
                                    ['open', 'confirm']),
                                'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/20' =>
                                    $status === 'processing',
                                'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20' => in_array(
                                    $status,
                                    ['completed', 'closed']),
                                'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20' =>
                                    $status === 'cancelled',
                            
                                // fallback
                                'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-500/20' => !in_array(
                                    $status,
                                    [
                                        'draft',
                                        'pending',
                                        'open',
                                        'confirm',
                                        'processing',
                                        'completed',
                                        'closed',
                                        'cancelled',
                                    ]),
                            ])>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100">INVOICE {{ $order->id }}</h3>

                        </div>
                    </header>

                    {{-- Order Info --}}
                    <section
                        class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 text-sm border-t border-gray-200 dark:border-gray-700 pb-4">
                        <div class="space-y-3">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Nama Pelanggan</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 capitalize">
                                    {{ $order->customer_name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No. Telepon</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 capitalize">
                                    {{ $order->customer_phone ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Alamat</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 capitalize">
                                    {{ $order->customer_address ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Tanggal</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 capitalize">
                                    {{ Carbon\Carbon::parse($order->order_date_time)->format('d M Y - H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Metode Pengiriman</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $order->delivery_method ? ucfirst(str_replace('_', ' ', $order->delivery_method)) : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Metode Pembayaran</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</p>
                            </div>
                        </div>
                    </section>

                    {{-- Items Table --}}
                    <div class="-mx-6 sm:-mx-8 lg:-mx-10 overflow-x-auto">
                        <table class="w-full rounded-lg overflow-hidden">
                            <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="p-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Barang
                                    </th>
                                    <th class="p-4 text-center text-sm font-semibold text-gray-900 dark:text-gray-200 w-20">
                                        Jml</th>
                                    <th class="p-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-200 w-28">
                                        Harga</th>
                                    <th class="p-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-200 w-32">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($order->orderItems as $item)
                                    <tr class="text-sm">
                                        <td class="p-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item->product->name ?? $item->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">SKU:
                                                {{ $item->product->id ?? 'N/A' }}</div>
                                        </td>
                                        <td class="p-4 text-center whitespace-nowrap text-gray-600 dark:text-gray-400">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="p-4 text-right whitespace-nowrap text-gray-600 dark:text-gray-400">
                                            {{ formatRupiah($item->price) }}
                                        </td>
                                        <td
                                            class="p-4 text-right whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                                            {{ formatRupiah($item->price * $item->quantity) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary --}}
                    <section class="mt-8 flex justify-end">
                        <div class="w-full max-w-md space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                <span
                                    class="font-medium text-gray-800 dark:text-gray-200">{{ formatRupiah($order->total_price) }}</span>
                            </div>
                            <div class=" my-2"></div>
                            <div class="flex justify-between font-bold text-lg">
                                <span class="text-gray-900 dark:text-gray-100">Total Keseluruhan</span>
                                <span
                                    class="text-gray-900 dark:text-gray-100">{{ formatRupiah($order->total_price) }}</span>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

            <div class="print-button-container mt-6 text-center">
                <x-filament::button icon="heroicon-o-printer" color="primary" onclick="window.print()">
                    Cetak Tagihan
                </x-filament::button>
            </div>
        </div>
    @endvolt

    <style>
        @media print {

            body,
            .fi-body,
            .fi-main-ctn {
                background: white !important;
                padding: 0 !important;
            }

            body * {
                visibility: hidden;
            }

            .invoice-printable,
            .invoice-printable * {
                visibility: visible;
            }

            .invoice-printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 1rem;
                background: white !important;
                color: black !important;
            }

            .print-button-container {
                display: none;
            }
        }
    </style>
</x-filament-panels::page>
