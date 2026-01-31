# Rector - Panduan Penggunaan

## Apa itu Rector?

Rector adalah tool refactoring otomatis untuk PHP yang membantu meningkatkan kualitas kode, memperbarui sintaks, dan menerapkan best practice secara konsisten.

## Instalasi

### Versi yang Digunakan

- **PHP**: 8.2+
- **Laravel**: 12.0
- **Rector**: 2.3.5

### Composer Scripts

```json
{
    "scripts": {
        "rector": "@php vendor/bin/rector process --dry-run",
        "rector-fix": "@php vendor/bin/rector process"
    }
}
```

## Konfigurasi

File konfigurasi: `rector.php`

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/app/Filament')
    ->withSkipPath(__DIR__ . '/vendor')
    // PHP Sets
    ->withPhpSets(php82: true)
    // Rector Sets
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::STRICT_BOOLEANS,
        SetList::NAMING,
    ])
    // PHPUnit sets
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
    ])
    ->withImportNames(false, true);
```

## Perintah Dasar

### 1. Cek Perubahan (Dry-Run)

Melihat perubahan yang akan dilakukan tanpa mengubah file:

```bash
composer rector
```

Atau:

```bash
php vendor/bin/rector process --dry-run
```

### 2. Terapkan Perubahan

Menerapkan perubahan ke kode:

```bash
composer rector-fix
```

Atau:

```bash
php vendor/bin/rector process
```

### 3. Proses File/Path Spesifik

Memproses file atau folder tertentu:

```bash
# Single file
php vendor/bin/rector process app/Services/FonnteService.php --dry-run

# Specific directory
php vendor/bin/rector process app/Services --dry-run

# Multiple paths
php vendor/bin/rector process app/Services app/Http/Controllers --dry-run
```

### 4. Show Progress

Menampilkan progress bar:

```bash
php vendor/bin/rector process --dry-run --progress
```

### 5. Clear Cache

Membersihkan cache Rector:

```bash
php vendor/bin/rector clear-cache
```

## Sets yang Digunakan

| Set | Deskripsi |
|-----|-----------|
| `DEAD_CODE` | Menghapus kode yang tidak terpakai |
| `CODE_QUALITY` | Meningkatkan kualitas kode |
| `CODING_STYLE` | Menyesuaikan style kode |
| `TYPE_DECLARATION` | Menambahkan type declarations |
| `PRIVATIZATION` | Mengubah visibility ke private jika memungkinkan |
| `EARLY_RETURN` | Menggunakan early return pattern |
| `INSTANCEOF` | Menyederhanakan instanceof checks |
| `STRICT_BOOLEANS` | Menggunakan strict boolean comparisons |
| `NAMING` | Menyesuaikan naming conventions |
| `PHP_82` | Fitur PHP 8.2 |
| `PHPUNIT_100` | PHPUnit 10.0+ |

## Contoh Perbaikan yang Dilakukan

### 1. Type Declarations

**Sebelum:**
```php
public function getAllDevices()
{
    return $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);
}
```

**Sesudah:**
```php
public function getAllDevices(): array
{
    return $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);
}
```

### 2. Arrow Function Return Type

**Sebelum:**
```php
collect($data['daily'])->map(fn ($day) => [
    $day['date'] ?? 'Unknown',
    $day['outgoing'] ?? 0,
])
```

**Sesudah:**
```php
collect($data['daily'])->map(fn ($day): array => [
    $day['date'] ?? 'Unknown',
    $day['outgoing'] ?? 0,
])
```

### 3. Explicit Boolean Compare

**Sebelum:**
```php
if (!$message) {
    // ...
}
```

**Sesudah:**
```php
if ($message === '' || $message === '0') {
    // ...
}
```

### 4. Variable Naming

**Sebelum:**
```php
foreach ($selections as $sel) {
    $index = $sel['index'];
}
```

**Sesudah:**
```php
foreach ($selections as $selection) {
    $index = $selection['index'];
}
```

### 5. Empty Array Check

**Sebelum:**
```php
if (empty($data)) {
    // ...
}
```

**Sesudah:**
```php
if ($data === []) {
    // ...
}
```

## Path yang Dilewati (Skip)

Beberapa path dikonfigurasi untuk dilewati agar tidak diproses:

- `app/Filament` - File generated oleh Filament
- `vendor` - Dependency pihak ketiga

Untuk menambahkan skip path:

```php
->withSkipPath(__DIR__ . '/app/Filament')
->withSkipPath(__DIR__ . '/app/CustomPath')
```

## Best Practices

### 1. Selalu Gunakan Dry-Run Terlebih Dahulu

Sebelum menerapkan perubahan, selalu cek dulu dengan dry-run:

```bash
composer rector
```

Review output dan pastikan perubahan sesuai.

### 2. Commit Sebelum Menjalankan Rector

Selalu commit kode sebelum menjalankan rector-fix:

```bash
git add .
git commit -m "Before rector changes"
composer rector-fix
```

### 3. Review Perubahan

Setelah rector-fix, review perubahan:

```bash
git diff
```

### 4. Jalankan Test

Setelah perubahan diterapkan, jalankan test untuk memastikan tidak ada yang rusak:

```bash
composer test
# atau
php artisan test
```

### 5. Integrasi dengan CI/CD

Tambahkan rector ke pipeline CI/CD:

```yaml
# .github/workflows/rector.yml
name: Rector

on: [push, pull_request]

jobs:
  rector:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - run: composer install --prefer-dist
      - run: composer rector
```

## Troubleshooting

### Error: Undefined constant

Jika ada error tentang undefined constant, cek set yang digunakan di `rector.php`. Pastikan set tersebut tersedia di versi Rector yang digunakan.

### Terlalu Banyak Perubahan

Jika rector menampilkan terlalu banyak perubahan, Anda bisa:

1. Proses per directory:
```bash
php vendor/bin/rector process app/Services --dry-run
```

2. Nonaktifkan sementara beberapa set di `rector.php`

### Konflik dengan Pint atau PHP-CS-Fixer

Jika menggunakan Pint atau PHP-CS-Fixer, jalankan dalam urutan berikut:

```bash
# 1. Rector dulu
composer rector-fix

# 2. Lalu Pint
./vendor/bin/pint

# 3. Atau gunakan script combined
composer fix-all
```

## Referensi

- [Rector Documentation](https://github.com/rectorphp/rector)
- [Rector Laravel](https://github.com/driftingly/rector-laravel)
- [Available Rector Rules](https://getrector.org/documentation/all-rules)
