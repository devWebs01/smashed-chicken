<?php

namespace App\Filament\Resources\Devices\Pages;

use App\Filament\Resources\Devices\DeviceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected ?string $heading = 'Tambah Perangkat';

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dasbor',
            static::getResource()::getUrl() => static::getResource()::getNavigationLabel(),
            static::getUrl() => $this->getHeading(),
        ];
    }

    protected string $view = 'filament.resources.devices.pages.create-device';
}
