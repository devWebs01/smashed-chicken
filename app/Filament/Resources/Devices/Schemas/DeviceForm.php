<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('token')
                    ->required(),
                TextInput::make('device')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
