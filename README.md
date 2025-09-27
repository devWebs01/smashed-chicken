# Sistem Pemesanan Ayam Geprek via WhatsApp

Proyek ini adalah sistem pemesanan makanan berbasis WhatsApp yang dibangun dengan Laravel, Filament, dan Fonnte API. Sistem ini memungkinkan pengguna untuk melihat menu, melakukan pemesanan, dan mengelola pesanan mereka sepenuhnya melalui WhatsApp. Proyek ini juga dilengkapi dengan panel admin yang komprehensif bagi pemilik toko untuk mengelola produk, melacak pesanan, dan mengawasi operasional.

## Fitur

- **Bot Pemesanan WhatsApp:**
  - **Menu Interaktif:** Pengguna dapat meminta menu produk langsung di WhatsApp.
  - **Pemesanan Terpandu:** Proses langkah demi langkah memandu pengguna dalam memilih produk, menentukan jumlah, dan mengonfirmasi pesanan mereka.
  - **Pendaftaran Pengguna:** Pengguna baru akan diminta untuk memberikan nama dan alamat mereka.
  - **Sintaks Pemesanan Fleksibel:** Mendukung berbagai format untuk pemesanan (misalnya, `1`, `1 2`, `1=2, 2=1`).
  - **Manajemen Pesanan:** Pengguna dapat menambahkan item ke pesanan sebelumnya (`tambah`), membatalkan pesanan yang tertunda (`batal`), dan mengatur ulang sesi mereka (`reset`).
  - **Pengiriman & Pembayaran:** Mendukung metode pembayaran `takeaway` (ambil sendiri) atau `delivery` (diantar) dan `cash` (tunai) atau `transfer`.
- **Panel Admin (Filament):**
  - **Dasbor:** Gambaran umum penjualan, dengan grafik untuk pesanan per hari.
  - **Manajemen Produk:** Antarmuka CRUD untuk mengelola item makanan (nama, deskripsi, harga, gambar).
  - **Manajemen Pesanan:** Melihat dan mengelola semua pesanan yang masuk, statusnya, dan detail pelanggan.
  - **Manajemen Perangkat:** Mengelola perangkat WhatsApp yang terhubung melalui Fonnte API.
  - **Manajemen Pengguna:** Mengelola pengguna admin.
- **Pengaturan Otomatis:**
  - Satu perintah (`php artisan whatsapp:setup`) untuk menginisialisasi database dan memandu melalui pengaturan ngrok.
  - Skrip shell (`./update_ngrok.sh`) untuk memperbarui file `.env` secara otomatis dengan URL webhook ngrok.

## Teknologi yang Digunakan

 - **Backend:** Laravel 12
 - **Panel Admin:** Filament 4
 - **Frontend:** Vite, Tailwind CSS
 - **Gateway WhatsApp:** [Fonnte API](https://fonnte.com/)
 - **Database:** SQLite (default), MySQL/MariaDB, atau DB lain yang didukung Laravel
 - **Tunneling Pengembangan:** Ngrok

## 📁 File Penting untuk Development

### **Scripts:**
- `ngrok-static.sh` - Jalankan ngrok dengan URL static (FILE UTAMA)
- `update_ngrok.sh` - Update webhook URL otomatis (untuk ngrok biasa)

### **Laravel Commands:**
- `php artisan whatsapp:setup` - Setup awal project
- `php artisan serve --host=0.0.0.0 --port=8000` - Jalankan development server

## 🌐 ngrok Static URL (Development)

Project ini menggunakan **ngrok static URL** untuk development yang lebih stabil:

### **URL Static Default:**
```
https://toad-current-humbly.ngrok-free.app
```

### **Quick Setup:**
```bash
# 1. Jalankan Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# 2. Di terminal baru, jalankan ngrok static
./ngrok-static.sh 8000

# 3. Webhook URL: https://toad-current-humbly.ngrok-free.app/webhook/whatsapp
```

### **Setup di Laptop Baru:**
```bash
# Clone project
git clone <repository>
cd geprek

# Jalankan ngrok static
./ngrok-static.sh 8000
```

> **Catatan:** Pastikan URL sudah di-reserve di [ngrok dashboard](https://dashboard.ngrok.com/domains) sebelum digunakan.

## Skema Database

Tabel database utama meliputi:
- `products`: Menyimpan semua produk yang tersedia.
- `orders`: Merekam semua pesanan pelanggan.
- `order_items`: Berisi produk dan jumlah spesifik untuk setiap pesanan.
- `devices`: Menyimpan kredensial perangkat WhatsApp Fonnte.
- `users`: Untuk akun pengguna panel admin.
- `settings`: Untuk pengaturan umum aplikasi.

### Diagram Hubungan Entitas (ERD)

```mermaid
erDiagram
    orders {
        int id PK
        string customer_name
        string customer_phone
        string customer_address
        datetime order_date_time
        string payment_method
        enum status
        decimal total_price
        enum delivery_method
    }

    order_items {
        int id PK
        int order_id FK
        int product_id FK
        int quantity
        decimal price
        decimal subtotal
    }

    products {
        int id PK
        string name
        text description
        decimal price
        string image
    }

    users {
        int id PK
        string name
        string email
    }

    devices {
        int id PK
        string name
        string token
        string device
        boolean is_active
    }

    settings {
        int id PK
        string key
        string value
    }

    orders ||--o{ order_items : "has"
    products ||--o{ order_items : "is part of"
```

## Pengaturan Pengembangan Lokal

### Prasyarat

- PHP 8.2+
- Composer
- Node.js & NPM
- Akun [Fonnte](https://fonnte.com/)
- [Ngrok](https://ngrok.com/) terinstal dan terautentikasi

### Langkah-langkah Instalasi

1.  **Clone repositori:**
    ```bash
    git clone <url-repositori>
    cd geprek
    ```

2.  **Instal dependensi:**
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Lingkungan:**
    - Salin file lingkungan contoh:
      ```bash
      cp .env.example .env
      ```
    - Buat kunci aplikasi:
      ```bash
      php artisan key:generate
      ```
    - Konfigurasikan koneksi database Anda di file `.env`. Default menggunakan SQLite (file `database/database.sqlite` akan dibuat otomatis). Untuk database lain seperti MySQL, konfigurasikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
    - Tambahkan Token Akun Fonnte Anda ke file `.env`:
      ```
      ACCOUNT_TOKEN=token_fonnte_anda
      ```

4.  **Jalankan Perintah Pengaturan Otomatis:**
    Perintah ini akan memeriksa dan membuat file database SQLite jika belum ada, melakukan migrasi database, mengisinya dengan data awal, dan memandu Anda melalui pengaturan ngrok jika diperlukan.
    ```bash
    php artisan whatsapp:setup
    ```

5.  **Jalankan Layanan:**
    - Jalankan server pengembangan Laravel:
      ```bash
      php artisan serve --host=0.0.0.0 --port=8000
      ```
    - Di terminal baru, jalankan ngrok dengan URL static (recommended):
      ```bash
      ./ngrok-static.sh 8000
      ```
    - Atau gunakan ngrok biasa (URL berubah setiap restart):
      ```bash
      ngrok http 8000
      ```

6.  **Atur URL Webhook:**
    - Jalankan skrip untuk memperbarui file `.env` dengan URL ngrok:
      ```bash
      ./update_ngrok.sh
      ```
    - Bersihkan cache konfigurasi:
      ```bash
      php artisan config:clear
      ```
    - Skrip akan menampilkan URL webhook. Salin URL ini dan tempelkan ke pengaturan webhook di [Dasbor Fonnte](https://fonnte.com/device) Anda. URL akan terlihat seperti ini: `https://<subdomain-ngrok-anda>.ngrok-free.app/webhook/whatsapp`.

    > **Catatan:** Jika menggunakan ngrok static, URL webhook adalah: `https://toad-current-humbly.ngrok-free.app/webhook/whatsapp`

7.  **Build Aset Frontend:**
    ```bash
    npm run dev
    ```

## Penggunaan

### Bot WhatsApp

- **Mulai percakapan:** Kirim pesan apa pun (misalnya, "halo") ke nomor WhatsApp yang terhubung dengan perangkat Fonnte Anda.
- **Lihat menu:** Kirim "menu".
- **Buat pesanan:** Ikuti petunjuk di layar.
  - Contoh 1 (Pesan satu porsi produk #1): `1`
  - Contoh 2 (Pesan tiga porsi produk #2): `2 3` atau `2=3`
  - Contoh 3 (Pesan dua porsi produk #1 dan satu produk #3): `1=2, 3=1`

### Panel Admin

- **URL:** `http://localhost:8000/admin`
- **Login:** Selama pengembangan lokal, Anda dapat menggunakan fitur login pengembang.
  - **Email:** `admin@testing.com`
  - **Password:** `password` (atau nilai apa pun, akan diabaikan)

## Troubleshooting

### **ngrok Issues:**

**❌ "ngrok tidak ditemukan":**
```bash
# Install ngrok
sudo snap install ngrok
ngrok config add-authtoken <your-token>
```

**❌ "URL static tidak bisa diakses":**
- Pastikan URL sudah di-reserve di ngrok dashboard
- Cek kuota ngrok (free tier terbatas)
- Restart ngrok: `./ngrok-static.sh 8000`

**❌ "Port 8000 sudah digunakan":**
```bash
# Cek proses yang menggunakan port
lsof -i :8000
# Bunuh proses jika perlu
kill -9 <PID>
```

### **WhatsApp Webhook Issues:**

**❌ "Webhook tidak merespons":**
- Pastikan Laravel server running di port 8000
- Cek ngrok tunnel aktif: `http://localhost:4040`
- Test webhook URL di browser

**❌ "Fonnte device tidak terhubung":**
- Cek token Fonnte di file `.env`
- Pastikan device aktif di dashboard Fonnte
- Test koneksi: `php artisan tinker` → `Fonnte::test()`

### **Database Issues:**

**❌ "Database connection failed":**
```bash
# Untuk SQLite
touch database/database.sqlite

# Untuk MySQL
php artisan config:clear
php artisan migrate
```

## 💡 Tips Praktis

### **Development Workflow Harian:**
```bash
# Terminal 1 - Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 - ngrok static (recommended)
./ngrok-static.sh 8000

# Terminal 3 - Update webhook URL
./update_ngrok.sh
```

### **Testing WhatsApp Bot:**
1. Pastikan ngrok running: `./ngrok-static.sh 8000`
2. Update webhook: `./update_ngrok.sh`
3. Setup webhook di Fonnte dengan URL yang ditampilkan
4. Test kirim pesan ke nomor WhatsApp yang terhubung

## Perintah Artisan yang Tersedia

 - `php artisan whatsapp:setup`: Perintah utama untuk pengaturan awal proyek. Memeriksa dan membuat file database SQLite jika diperlukan, menjalankan migrasi, seeding, dan pengaturan ngrok.
 - `php artisan chain:run --name=setup`: Menjalankan rantai migrasi dan seeding (digunakan oleh perintah setup).

---

## 🎯 Quick Reference

| Task | Command/File |
|------|-------------|
| **Setup awal** | `php artisan whatsapp:setup` |
| **Run server** | `php artisan serve --host=0.0.0.0 --port=8000` |
| **Run ngrok static** | `./ngrok-static.sh 8000` |
| **Update webhook** | `./update_ngrok.sh` |
| **Admin panel** | `http://localhost:8000/admin` |
| **Webhook URL** | `https://toad-current-humbly.ngrok-free.app/webhook/whatsapp` |
