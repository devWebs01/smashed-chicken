<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected ?string $heading = 'Buat Produk';

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dasbor',
            static::getResource()::getUrl() => static::getResource()::getNavigationLabel(),
            static::getUrl() => $this->getHeading(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
