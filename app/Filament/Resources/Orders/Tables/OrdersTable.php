<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Nama Customer')
                    ->sortable()
                    ->limit(25)
                    ->searchable(),
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->formatStateUsing(fn ($state) => formatRupiah($state))
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'gray' => 'draft',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Tertunda',
                        'processing' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'confirm' => 'Dikonfirmasi',
                        'draft' => 'Draf',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('delivery_method')
                    ->label('Metode Pengiriman')
                    ->colors([
                        'primary' => 'dine_in',
                        'warning' => 'takeaway',
                        'success' => 'delivery',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'dine_in' => 'Makan di Tempat',
                        'takeaway' => 'Bawa Pulang',
                        'delivery' => 'Diantar',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->label('Lihat'),
                ViewAction::make()->button()->label('Cetak'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
