<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $heading = 'Daftar Pengguna';

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
            CreateAction::make()
                ->label('Buat Pengguna'),
        ];
    }
}
