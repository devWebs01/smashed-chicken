<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected ?string $heading = 'Daftar Produk';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Produk'),
        ];
    }
}
