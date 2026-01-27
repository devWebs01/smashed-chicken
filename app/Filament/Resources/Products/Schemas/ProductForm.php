<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga Produk')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),
                        Textarea::make('description')
                            ->label('Deskripsi Produk')
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->label('Gambar Produk')
                            ->image()
                            ->columnSpanFull()
                            ->disk('public'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
