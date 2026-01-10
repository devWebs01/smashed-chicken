<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = true;

    public function getHeading(): string
    {
        return 'Pesanan Terbaru';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with([
                        'orderItems.product' => function ($query) {
                            $query->select('id', 'name');
                        },
                        'device',
                    ])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_method')
                    ->label('Pengiriman')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivery' => 'warning',
                        'takeaway' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'confirm' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'processing' => 'Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'confirm' => 'Dikonfirmasi',
                        'draft' => 'Draft',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pesan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orderItems')
                    ->label('Items')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $items = collect($state);
                        if ($items->isEmpty()) {
                            return '-';
                        }

                        return $items->map(function ($item) {
                            // Debug: check if item is what we expect
                            if (! is_object($item) || ! isset($item->quantity)) {
                                return 'Item Invalid';
                            }

                            $productName = 'Produk Tidak Ditemukan';
                            if (isset($item->product) && is_object($item->product) && isset($item->product->name)) {
                                $productName = $item->product->name;
                            }

                            return $productName.' x'.$item->quantity;
                        })->join(', ');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
