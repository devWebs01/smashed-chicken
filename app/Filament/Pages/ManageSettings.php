<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UnitEnum;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Data';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();
        $this->data = $setting ? $setting->toArray() : [];
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('')
                ->schema([
                    TextInput::make('data.name')
                        ->label('Nama Aplikasi')
                        ->required(),

                    TextInput::make('data.phone')
                        ->numeric()
                        ->required()
                        ->label('Telepon'),

                    FileUpload::make('data.logo')
                        ->required()
                        ->label('Logo Aplikasi')
                        ->disk('public')
                        ->directory('setting')
                        ->image(),

                    Textarea::make('data.address')
                        ->required()
                        ->label('Alamat')
                        ->columnSpanFull()
                        ->rows(5),

                ])
                ->columns(1)
                ->columnSpanFull(),
        ];
    }

    // OPTION: tetap sediakan tombol action, tapi arahkan ke method 'save'
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->button()
                ->action('save'), // -> memanggil public method save()
        ];
    }

    // PUBLIC method agar Livewire/Blade bisa memanggilnya (fix error)
    public function save(): void
    {
        // 1) ambil seluruh state form
        $state = $this->form->getState() ?? [];

        // 2) ambil inner payload: prefer $state['data'] jika ada
        $payload = $state['data'] ?? $state;

        // 3) normalisasi logo (FileUpload kadang menghasilkan array atau string)
        if (isset($payload['logo'])) {
            $logo = $payload['logo'];

            // jika FileUpload mengembalikan array (mis. multiple / struktur), ambil elemen pertama
            if (is_array($logo)) {
                // cari string path di dalam array
                $firstString = Arr::first($logo, function ($value) {
                    return is_string($value) && ! Str::contains($value, 'data:'); // simplicity
                });
                $payload['logo'] = $firstString ?? (string) Arr::first($logo);
            }

            // jika null/empty -> hapus agar tidak menimpa nilai lama
            if (empty($payload['logo'])) {
                unset($payload['logo']);
            }
        }

        // 4) pastikan model Setting mengizinkan mass assign (cek $fillable)
        // jika tidak, gunakan assign field-by-field
        $setting = Setting::first();

        if ($setting) {
            // Jika Setting::$fillable sudah diset, update massal aman
            try {
                $setting->update($payload);
            } catch (\Throwable $e) {
                // fallback: assign field-by-field
                foreach ($payload as $key => $value) {
                    if (in_array($key, $setting->getFillable())) {
                        $setting->{$key} = $value;
                    }
                }
                $setting->save();
            }
        } else {
            // create new (pastikan Setting::$fillable ada)
            Setting::create($payload);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();

        // refill form state dari DB agar tampil konsisten
        $this->data = Setting::first()?->toArray() ?? [];
        $this->form->fill(['data' => $this->data]);
    }
}
