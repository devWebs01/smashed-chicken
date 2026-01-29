# Fitur Sinkronisasi Device Fonnte

## Deskripsi
Fitur ini memungkinkan Anda untuk menyinkronkan data device dari Fonnte ke database lokal aplikasi. Fitur ini berguna ketika ACCOUNT_TOKEN diubah atau ketika ada device yang ditambahkan/ dihapus di dashboard Fonnte tetapi belum terupdate di database lokal.

## Cara Penggunaan

### 1. Melalui Interface Admin
1. Buka halaman **Manajemen Data → Perangkat**
2. Klik tombol **"Sinkronkan Data"** di bagian atas halaman
3. Tunggu hingga proses sinkronisasi selesai
4. Sistem akan menampilkan notifikasi:
   - Jika berhasil: Menampilkan jumlah device yang berhasil disinkronkan
   - Jika ada error: Menampilkan detail error untuk setiap device yang gagal

### 2. Melalui API (Manual)
```bash
# Endpoint untuk sinkronisasi
POST /admin/devices/sync

# Contoh response yang berhasil
{
    "success": true,
    "message": "Sinkronisasi berhasil dilakukan",
    "data": {
        "synced_count": 5,
        "total_devices": 5,
        "errors": []
    }
}

# Contoh response dengan error
{
    "success": true,
    "message": "Sinkronisasi berhasil dilakukan",
    "data": {
        "synced_count": 4,
        "total_devices": 5,
        "errors": [
            {
                "device": "6281234567890",
                "error": "Device already exists in local database"
            }
        ]
    }
}
```

## Proses Sinkronisasi
1. Mengambil semua device dari API Fonnte menggunakan `getAllDevices()`
2. Membandingkan dengan data di database lokal
3. Untuk setiap device:
   - Jika tidak ada di local: Membuat record baru
   - Jika sudah ada: Update data (nama, token, status)
4. Mengembalikan hasil statistik sinkronisasi

## Troubleshooting

### Error yang Sering Terjadi
1. **"Invalid or empty API token"**
   - Pastikan ACCOUNT_TOKEN di .env sudah benar
   - Token harus diawali dengan `8uk...` (format Fonnte)

2. **"Connection timeout"**
   - Periksa koneksi internet
   - Coba lagi beberapa saat kemudian

3. **"Device already exists"**
   - Biasanya terjadi pada device yang sudah ada di database
   - System akan melewati device ini secara otomatis

## Best Practices
1. Sinkronisasi dilakukan secara berkala setiap ada perubahan di Fonnte
2. Past ACCOUNT_TOKEN selalu update jika diubah di dashboard Fonnte
3. Periksa log error jika sinkronisasi gagal
4. Backup database sebelum melakukan sinkronisasi massal

## File yang Terkait
- `app/Services/FonnteService.php` - Method `syncDevices()`
- `app/Http/Controllers/DeviceSyncController.php` - Controller sinkronisasi
- `routes/web.php` - Route endpoint
- `resources/views/filament/resources/devices/pages/list-device.blade.php` - UI tombol sinkronisasi