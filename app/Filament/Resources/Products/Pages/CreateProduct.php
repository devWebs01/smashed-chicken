<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected ?string $heading = 'Buat Produk';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
