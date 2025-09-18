<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        DateTimePicker::make('email_verified_at')
                            ->default(now())
                            ->hidden(),
                        TextInput::make('password')
                            ->label('Kata Sandi')

                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null) // hanya simpan kalau diisi
                            ->dehydrated(fn ($state) => filled($state)) // kalau kosong, jangan update field
                            ->required(fn (string $context): bool => $context === 'create') // wajib saat create saja
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
