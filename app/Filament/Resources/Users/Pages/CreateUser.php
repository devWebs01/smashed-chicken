<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected ?string $heading = 'Buat Pengguna';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
