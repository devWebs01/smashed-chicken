<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 5;

    public function getHeading(): string
    {
        return 'Pesanan Terbaru';
    }

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = true;

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

            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
