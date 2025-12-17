# 🚀 Setup Cloudflare Tunnel untuk Webhook WhatsApp

Panduan lengkap menggunakan Cloudflare Tunnel sebagai pengganti ngrok untuk webhook WhatsApp.

## 📋 Daftar Isi

1. [Kenapa Cloudflare Tunnel?](#kenapa-cloudflare-tunnel)
2. [Prerequisites](#prerequisites)
3. [Instalasi Cloudflared](#instalasi-cloudflared)
4. [Konfigurasi Project](#konfigurasi-project)
5. [Cara Menggunakan](#cara-menggunakan)
6. [Testing Webhook](#testing-webhook)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Kenapa Cloudflare Tunnel?

### ✅ Keuntungan vs ngrok:

- **URL Static yang Pendek**: `local.testingbae0000.my.id` vs `toad-current-humbly.ngrok-free.app`
- **Gratis Selamanya**: Tidak ada batasan waktu atau koneksi
- **Mudah Digunakan**: Tidak perlu command panjang berulang kali
- **Lebih Stabil**: Cocok untuk webhook yang memerlukan uptime tinggi
- **Domain Custom**: Bisa menggunakan domain sendiri

### ❌ Masalah dengan ngrok:

```bash
# Command yang panjang dan repetitif:
ngrok http --url=toad-current-humbly.ngrok-free.app 8000
```

### ✅ Solusi dengan Cloudflare Tunnel:

```bash
# Simple command:
./cloudflare-tunnel.sh 8000
```

---

## 📦 Prerequisites

1. **Laravel Server** berjalan di port 8000 (atau port lain yang Anda tentukan)
2. **Akun Cloudflare** (gratis)
3. **Domain** yang dikelola di Cloudflare (dalam contoh ini: `testingbae0000.my.id`)

---

## 💻 Instalasi Cloudflared

### Ubuntu/Debian:

```bash
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb
```

### Arch Linux:

```bash
yay -S cloudflared-bin
```

### Verifikasi Instalasi:

```bash
cloudflared --version
```

---

## ⚙️ Konfigurasi Project

### 1. File yang Sudah Dikonfigurasi:

#### `.env`
```env
APP_URL=https://local.testingbae0000.my.id
NGROK_WEBHOOK_URL=https://local.testingbae0000.my.id
```

#### `bootstrap/app.php`
Trusted proxies sudah ditambahkan untuk menangani request dari Cloudflare Tunnel:
```php
$middleware->trustProxies(
    at: '*',
    headers: Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
             Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
             Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
             Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
             Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
);
```

#### `routes/web.php`
Route webhook sudah terdaftar:
```php
Route::match(['get', 'post'], '/webhook/whatsapp', WhatsAppController::class);
```

#### `bootstrap/app.php` - CSRF Exception
Webhook sudah di-exclude dari CSRF protection:
```php
$middleware->validateCsrfTokens(except: [
    'webhook/whatsapp',
]);
```

---

## 🚀 Cara Menggunakan

### Step 1: Jalankan Laravel Server

```bash
php artisan serve --port=8000
```

**Output yang diharapkan:**
```
INFO  Server running on [http://127.0.0.1:8000].  
Press Ctrl+C to stop the server
```

### Step 2: Jalankan Cloudflare Tunnel (Terminal Baru)

```bash
./cloudflare-tunnel.sh 8000
```

**Atau tanpa parameter (default port 8000):**
```bash
./cloudflare-tunnel.sh
```

**Output yang diharapkan:**
```
🚀 Menjalankan Cloudflare Tunnel
🌐 Domain: https://local.testingbae0000.my.id
🔌 Port: 8000

📋 Webhook URL untuk Fonnte:
   https://local.testingbae0000.my.id/webhook/whatsapp

💡 Tips:
   - Pastikan Laravel server berjalan di port 8000
   - Jalankan: php artisan serve --port=8000
   - Update webhook URL di dashboard Fonnte

[Tunnel logs akan muncul di sini]
```

### Step 3: Test Webhook

**Di terminal ketiga**, jalankan test script:

```bash
./test-webhook.sh
```

**Atau test URL spesifik:**
```bash
./test-webhook.sh https://local.testingbae0000.my.id/webhook/whatsapp
```

### Step 4: Update Webhook di Fonnte

1. Login ke [dashboard Fonnte](https://api.fonnte.com/)
2. Pilih device WhatsApp Anda
3. Update webhook URL ke:
   ```
   https://local.testingbae0000.my.id/webhook/whatsapp
   ```
4. Save dan test dengan mengirim pesan ke nomor bot

---

## 🧪 Testing Webhook

### Test Manual dengan curl:

#### GET Request:
```bash
curl -X GET https://local.testingbae0000.my.id/webhook/whatsapp
```

#### POST Request (Test Data):
```bash
curl -X POST https://local.testingbae0000.my.id/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

#### POST Request (Simulasi Fonnte):
```bash
curl -X POST https://local.testingbae0000.my.id/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6281234567890",
    "sender": "6281234567890",
    "message": "menu",
    "member": {
      "jid": "6281234567890@s.whatsapp.net",
      "name": "Test User"
    }
  }'
```

### Test Otomatis:

Gunakan script yang sudah disediakan:
```bash
./test-webhook.sh
```

Script ini akan menjalankan 3 test otomatis:
1. ✅ GET Request
2. ✅ POST Request dengan test data
3. ✅ POST Request simulasi webhook Fonnte

---

## 🔍 Troubleshooting

### 1. Webhook Tidak Menerima Request

**Cek apakah Laravel server berjalan:**
```bash
curl http://localhost:8000/webhook/whatsapp
```

**Cek route terdaftar:**
```bash
php artisan route:list | grep webhook
```

**Expected output:**
```
GET|POST|HEAD webhook/whatsapp .......................... WhatsAppController
```

### 2. Error 404 Not Found

**Cek APP_URL di `.env`:**
```bash
cat .env | grep APP_URL
```

**Expected:**
```
APP_URL=https://local.testingbae0000.my.id
```

### 3. Error 419 CSRF Token Mismatch

Pastikan webhook di-exclude dari CSRF:
```bash
cat bootstrap/app.php | grep -A 3 "validateCsrfTokens"
```

**Expected:**
```php
$middleware->validateCsrfTokens(except: [
    'webhook/whatsapp',
]);
```

### 4. Cloudflared Tidak Ditemukan

Install cloudflared:
```bash
# Ubuntu/Debian
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# Arch Linux
yay -S cloudflared-bin
```

### 5. Request Timeout atau Connection Refused

**Cek trusted proxies di `bootstrap/app.php`:**
```bash
cat bootstrap/app.php | grep -A 10 "trustProxies"
```

Pastikan konfigurasi trusted proxies ada.

### 6. Cloudflare Tunnel Error

**Restart tunnel:**
1. Stop tunnel dengan `Ctrl+C`
2. Jalankan kembali: `./cloudflare-tunnel.sh 8000`

### 7. Webhook Menerima Request tapi Tidak Diproses

**Cek Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

**Cek controller:**
```bash
cat app/Http/Controllers/WhatsAppController.php
```

---

## 📊 Monitoring

### Real-time Laravel Logs:
```bash
tail -f storage/logs/laravel.log
```

### Real-time Tunnel Logs:
Tunnel logs akan muncul di terminal tempat Anda menjalankan `./cloudflare-tunnel.sh`

### Test Endpoint Status:
```bash
curl -I https://local.testingbae0000.my.id/webhook/whatsapp
```

---

## 🎯 Quick Reference

### Start Development:
```bash
# Terminal 1: Laravel Server
php artisan serve --port=8000

# Terminal 2: Cloudflare Tunnel
./cloudflare-tunnel.sh 8000

# Terminal 3: Test (optional)
./test-webhook.sh
```

### Webhook URL:
```
https://local.testingbae0000.my.id/webhook/whatsapp
```

### Local Test URL:
```
http://localhost:8000/webhook/whatsapp
```

---

## 📚 Resources

- [Cloudflare Tunnel Documentation](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [Laravel Trusted Proxies](https://laravel.com/docs/requests#configuring-trusted-proxies)
- [Fonnte API Documentation](https://api.fonnte.com/docs)

---

## ✨ Tips & Tricks

1. **Gunakan tmux atau screen** untuk menjalankan Laravel server dan tunnel di background
2. **Bookmark webhook URL** di dashboard Fonnte untuk akses cepat
3. **Monitor logs** secara real-time saat testing
4. **Simpan test-webhook.sh** untuk debugging cepat
5. **Dokumentasikan custom domain** jika menggunakan domain berbeda

---

## 🤝 Support

Jika mengalami masalah:

1. Cek troubleshooting section di atas
2. Verifikasi semua konfigurasi sudah sesuai
3. Test menggunakan `./test-webhook.sh`
4. Cek Laravel logs untuk error detail

---

**Happy Coding! 🚀**
