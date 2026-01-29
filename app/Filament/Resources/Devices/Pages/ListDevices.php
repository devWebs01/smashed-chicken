<?php

namespace App\Filament\Resources\Devices\Pages;

use App\Filament\Resources\Devices\DeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url('/admin') => 'Dasbor',
            static::getUrl() => $this->getHeading(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Perangkat'),
        ];
    }

    protected ?string $heading = 'Daftar Perangkat';

    protected string $view = 'filament.resources.devices.pages.list-device';
}
