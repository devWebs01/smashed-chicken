# 🍛 Geprek - Sistem Pemesanan Makanan WhatsApp

Aplikasi pemesanan makanan berbasis WhatsApp dengan dashboard admin menggunakan Laravel dan Filament.

## ✨ Fitur Utama

- **Pemesanan WhatsApp:** Pesanan masuk langsung dari pesan WhatsApp
- **Dashboard Admin:**
  - Gambaran umum penjualan, dengan grafik untuk pesanan per hari
  - Manajemen Produk (CRUD): nama, deskripsi, harga, gambar
  - Manajemen Pesanan: melihat dan mengelola semua pesanan, status, detail pelanggan
  - Manajemen Perangkat: mengelola perangkat WhatsApp yang terhubung melalui Fonnte API
  - Manajemen Pengguna: mengelola pengguna admin
- **Pengaturan Otomatis:**
  - Satu perintah (`php artisan whatsapp:setup`) untuk menginisialisasi database

## Teknologi yang Digunakan

- **Backend:** Laravel 12
- **Panel Admin:** Filament 4
- **Frontend:** Vite, Tailwind CSS
- **Gateway WhatsApp:** [Fonnte API](https://fonnte.com/)
- **Database:** SQLite (default), MySQL/MariaDB, atau DB lain yang didukung Laravel
- **Tunneling Pengembangan:** Cloudflare Tunnel

## 📁 File Penting untuk Development

### **Scripts:**
- `cloudflare-tunnel.sh` - Jalankan Cloudflare Tunnel (RECOMMENDED) ⭐
- `test-webhook.sh` - Test webhook secara otomatis

### **Laravel Commands:**
- `php artisan whatsapp:setup` - Setup awal project
- `php artisan serve --port=8000` - Jalankan development server

## 🌐 Tunneling Options untuk Development

### **Option 1: Cloudflare Tunnel (RECOMMENDED)**

**Keuntungan:**
- URL yang konsipien dan mudah diingat
- Gratis dan tidak perlu autentikasi
- Koneksi yang lebih stabil dan cepat

**Cara Menggunakan:**
```bash
# 1. Di terminal pertama, jalankan Laravel
php artisan serve --port=8000

# 2. Di terminal baru, jalankan Cloudflare Tunnel
./cloudflare-tunnel.sh 8000

# 3. Webhook URL: https://local.systemwebsite.my.id/webhook/whatsapp
```

📚 **[Panduan Lengkap Cloudflare Tunnel](documentation/QUICK-START-CLOUDFLARE.md)**

## 🚀 Instalasi & Setup

### Prasyarat

- PHP 8.2+
- Composer
- Node.js & NPM
- Akun [Fonnte](https://fonnte.com/)

### Langkah-langkah Instalasi

1.  **Clone Repository:**
    ```bash
    git clone https://github.com/your-username/geprek.git
    cd geprek
    ```

2.  **Install Dependencies PHP:**
    ```bash
    composer install
    ```

3.  **Install Dependencies JavaScript:**
    ```bash
    npm install
    ```

4.  **Salin File Konfigurasi:**
    ```bash
    cp .env.example .env
    ```

5.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

6.  **Konfigurasi Database dan WhatsApp:**
    Edit file `.env` dan atur:
    ```
    DB_CONNECTION=sqlite
    ACCOUNT_TOKEN=your_fonnte_token
    APP_URL=https://your-public-domain.com
    ```

7.  **Jalankan Perintah Pengaturan Otomatis:**
    Perintah ini akan memeriksa dan membuat file database SQLite jika belum ada, melakukan migrasi database, mengisinya dengan data awal.
    ```bash
    php artisan whatsapp:setup
    ```

8.  **Setup Tunneling untuk Development:**
    Untuk development lokal, Anda perlu mengekspos aplikasi ke internet:

    - **Menggunakan Cloudflare Tunnel (RECOMMENDED):**
      ```bash
      ./cloudflare-tunnel.sh 8000
      ```

    Webhook URL: `https://local.systemwebsite.my.id/webhook/whatsapp`

9.  **Set Webhook di Fonnte:**
    - Salin URL webhook di dashboard admin atau dari output script
    - Tempelkan ke pengaturan webhook di [Dasbor Fonnte](https://md.fonnte.com/new/device.php) Anda

10. **Build Aset Frontend:**
    ```bash
    npm run build
    ```

11. **Jalankan Aplikasi:**
    ```bash
    php artisan serve
    ```

## 📱 Cara Penggunaan

### Untuk Pelanggan (WhatsApp)
1. Kirim pesan ke nomor WhatsApp terdaftar
2. Format pesan:
   - `menu` - Menampilkan daftar menu
   - `pesan [nama_menu] [jumlah]` - Memesan item (contoh: `pesan ayam geprek 2`)
   - `keranjang` - Melihat keranjang pesanan
   - `checkout` - Menyelesaikan pesanan
   - `help` - Bantuan

### Untuk Admin (Dashboard)
1. Login ke dashboard admin
2. Kelola produk: tambah, edit, hapus menu
3. Pantau pesanan masuk dan ubah status
4. Kelola perangkat WhatsApp
5. Lihat laporan penjualan

## 🔧 Konfigurasi Webhook

### Production
- Set `APP_URL` di .env dengan domain production Anda
- Set webhook Fonnte ke: `https://yourdomain.com/webhook/whatsapp`

### Development
- Gunakan Cloudflare Tunnel untuk mendapatkan URL lokal yang bisa diakses dari internet
- Set webhook Fonnte ke URL tunnel + `/webhook/whatsapp`

## 📊 Monitoring

### Log Error
- Cek log Laravel: `storage/logs/laravel.log`
- Dashboard admin akan menampilkan notifikasi untuk error webhook

### Testing Webhook
- Gunakan fitur "Test Webhook" di dashboard admin
- Atau gunakan script: `./test-webhook.sh`

## 🐛 Troubleshooting

### Umum
- **Webhook tidak terkirim:** Periksa apakah APP_URL sudah benar dan webhook di Fonnte sudah diset
- **Gagal terhubung ke Fonnte:** Cek ulang token Fonnte di .env
- **Database error:** Pastikan migrasi berhasil dan database SQLite terbuat

### Database
- Untuk reset database: hapus `database/database.sqlite` dan jalankan `php artisan migrate:fresh --seed`

### Log Debug
- Aktifkan debug mode di .env: `APP_DEBUG=true`
- Cek log untuk detail error

## 📚 Dokumentasi

- **[Quick Start Cloudflare Tunnel](documentation/QUICK-START-CLOUDFLARE.md)** - Setup cepat dalam 3 langkah ⭐
- **[Panduan Lengkap Cloudflare Tunnel](documentation/CLOUDFLARE-TUNNEL-SETUP.md)** - Instalasi, konfigurasi, troubleshooting
- **[API Documentation](documentation/)** - Coming soon

## 💡 Tips Praktis

1. Selalu gunakan Cloudflare Tunnel untuk development lokal
2. Test webhook setelah mengganti konfigurasi
3. Backup database secara teratur
4. Monitor log error secara berkala
5. Update package Laravel dan dependensi secara teratur

## 🤝 Kontribusi

Contributions welcome! Please feel free to submit a Pull Request.

## 📄 Lisensi

MIT License

---

Built with ❤️ using Laravel