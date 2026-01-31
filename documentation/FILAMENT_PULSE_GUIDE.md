# Filament Laravel Pulse - Guide

## Apa itu Filament Laravel Pulse?

Filament Laravel Pulse adalah plugin Filament yang mengintegrasikan **Laravel Pulse** (monitoring tool resmi Laravel) ke dalam dashboard Filament Anda. Plugin ini menyediakan widget untuk memantau performa aplikasi secara real-time.

## Fitur

| Widget | Deskripsi |
|--------|-----------|
| **PulseServers** | Monitoring performa server (CPU, Memory, dll) |
| **PulseCache** | Monitor penggunaan cache dan performa |
| **PulseExceptions** | Tracking exception dan error |
| **PulseUsage** | Analitik penggunaan aplikasi |
| **PulseQueues** | Monitoring job queue dan processing time |
| **PulseSlowJobs** | Menampilkan job yang lambat |
| **PulseSlowQueries** | Query database yang lambat |
| **PulseSlowRequests** | HTTP request yang lambat |
| **PulseSlowOutGoingRequests** | Outgoing HTTP request yang lambat |

## Instalasi

### 1. Install Laravel Pulse

```bash
composer require laravel/pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate --step
```

### 2. Install Filament Laravel Pulse

Untuk **Filament 4.x**:

```bash
composer require dotswan/filament-laravel-pulse:^2.0
```

Untuk **Filament 3.x**:

```bash
composer require dotswan/filament-laravel-pulse:^1.1.7
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --provider="Dotswan\FilamentLaravelPulse\FilamentLaravelPulseServiceProvider"
```

### 4. Update Dashboard Class

Edit `app/Filament/Pages/Dashboard.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Dotswan\FilamentLaravelPulse\Widgets\PulseCache;
use Dotswan\FilamentLaravelPulse\Widgets\PulseExceptions;
use Dotswan\FilamentLaravelPulse\Widgets\PulseQueues;
use Dotswan\FilamentLaravelPulse\Widgets\PulseServers;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowJobs;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowOutGoingRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowQueries;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseUsage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\ActionSize;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return 12;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('1h')
                    ->label('1 Hour')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard'))),
                Action::make('24h')
                    ->label('24 Hours')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard', ['period' => '24_hours']))),
                Action::make('7d')
                    ->label('7 Days')
                    ->action(fn () => $this->redirect(route('filament.admin.pages.dashboard', ['period' => '7_days']))),
            ])
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->size(ActionSize::Small)
                ->color('gray')
                ->button(),
        ];
    }

    public function getWidgets(): array
    {
        return [
            PulseServers::class,
            PulseCache::class,
            PulseExceptions::class,
            PulseUsage::class,
            PulseQueues::class,
            PulseSlowJobs::class,
            PulseSlowQueries::class,
            PulseSlowRequests::class,
            PulseSlowOutGoingRequests::class,
        ];
    }

    public function getVisibleWidgets(): array
    {
        return $this->getWidgets();
    }
}
```

### 5. Clear Cache

```bash
php artisan config:clear
php artisan view:clear
php artisan filament:cache-components
```

## Konfigurasi

### File Konfigurasi

**Laravel Pulse**: `config/pulse.php`
**Filament Pulse**: `config/filament-laravel-pulse.php`

### Menyesuaikan Widget

Anda dapat mengkonfigurasi setiap widget di `config/filament-laravel-pulse.php`:

```php
return [
    'components' => [
        'cache' => [
            'columnSpan' => ['md' => 5, 'xl' => 5],
            'rows' => 2,
            'cols' => 'full',
            'ignoreAfter' => '1 day',
            'isLazy' => true,
            'canView' => true,
        ],
        // ... komponen lainnya
    ],
];
```

### Opsi Konfigurasi

| Opsi | Deskripsi |
|------|-----------|
| `columnSpan` | Lebar kolom widget |
| `rows` | Tinggi baris widget |
| `cols` | Kolom layout |
| `ignoreAfter` | Abaikan data setelah periode waktu |
| `isLazy` | Load widget secara lazy |
| `canView` | Permission untuk melihat widget |

## Environment Variables

Tambahkan ke `.env`:

```env
# Pulse Configuration
PULSE_ENABLED=true
PULSE_PATH=pulse
PULSE_DOMAIN=null

# Storage Driver (database atau redis)
PULSE_STORAGE_DRIVER=database

# Ingest Rate (records per minute)
PULSE_INGEST_RATE=60
```

## Penggunaan

### Dashboard Filters

Gunakan tombol filter di dashboard untuk mengubah periode waktu:

- **1h** - Data 1 jam terakhir
- **24h** - Data 24 jam terakhir
- **7d** - Data 7 hari terakhir

### Access Pulse Dashboard

1. Login ke Filament Admin: `https://your-domain.com/admin`
2. Dashboard akan menampilkan semua Pulse widgets

### Pulse Native Dashboard

Anda juga dapat mengakses dashboard Pulse asli:

```
https://your-domain.com/pulse
```

## Troubleshooting

### Widget tidak muncul

1. Clear cache:
```bash
php artisan config:clear
php artisan view:clear
php artisan filament:cache-components
```

2. Pastikan Pulse enabled:
```env
PULSE_ENABLED=true
```

3. Jalankan migration:
```bash
php artisan migrate
```

### Data tidak tampil

Pastikan recorders aktif di `config/pulse.php`:

```php
'enabled' => env('PULSE_ENABLED', true),

'recorders' => [
    Recorders\CacheInteractions::class,
    Recorders\Exceptions::class,
    Recorders\Jobs::class,
    Recorders\OutgoingRequests::class,
    Recorders\Queues::class,
    Recorders\SlowJobs::class,
    Recorders\SlowQueries::class,
    Recorders\SlowRequests::class,
    Recorders\UserRequests::class,
],
```

### Permission Error

Tambahkan gate untuk Pulse di `App\Providers\AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewPulse', fn (User $user) => $user->hasRole('super_admin'));
}
```

## Tips Best Practices

1. **Production Use**: Gunakan Redis untuk storage driver di production
2. **Data Retention**: Atur `ignoreAfter` untuk menjaga ukuran database
3. **Lazy Loading**: Aktifkan `isLazy` untuk performa lebih baik
4. **Permission**: Batasi akses Pulse ke admin saja
5. **Monitoring**: Setel alarm untuk slow queries dan exceptions

## Referensi

- [Laravel Pulse Documentation](https://laravel.com/docs/pulse)
- [Filament PHP](https://filamentphp.com/)
- [dotswan/filament-laravel-pulse](https://github.com/dotswan/filament-laravel-pulse)
