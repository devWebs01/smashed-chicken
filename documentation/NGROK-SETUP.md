# 🔧 Setup ngrok untuk Webhook WhatsApp

Panduan menggunakan ngrok sebagai alternatif Cloudflare Tunnel untuk webhook development.

## 📋 Daftar Isi

1. [Kapan Menggunakan ngrok?](#kapan-menggunakan-ngrok)
2. [Prerequisites](#prerequisites)
3. [Instalasi ngrok](#instalasi-ngrok)
4. [Setup ngrok Static URL](#setup-ngrok-static-url)
5. [Cara Menggunakan](#cara-menggunakan)
6. [Troubleshooting](#troubleshooting)

---

## 🤔 Kapan Menggunakan ngrok?

### ✅ Gunakan ngrok jika:
- Tidak bisa menggunakan Cloudflare Tunnel
- Sudah familiar dengan ngrok
- Sudah punya ngrok static URL berbayar
- Perlu features ngrok premium (inspect, replay, etc.)

### ❌ Gunakan Cloudflare Tunnel jika:
- Ingin URL static gratis selamanya
- Command lebih simple
- Tidak perlu features ngrok khusus

> **💡 Rekomendasi:** Untuk kemudahan dan gratis, gunakan [Cloudflare Tunnel](QUICK-START-CLOUDFLARE.md)

---

## 📦 Prerequisites

1. **Laravel Server** berjalan di port 8000
2. **Akun ngrok** (gratis atau berbayar)
3. **ngrok authtoken** dari [ngrok dashboard](https://dashboard.ngrok.com/)

---

## 💻 Instalasi ngrok

### Ubuntu/Debian:

```bash
# Download ngrok
curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null
echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | sudo tee /etc/apt/sources.list.d/ngrok.list
sudo apt update
sudo apt install ngrok
```

### Arch Linux:

```bash
yay -S ngrok
```

### Menggunakan snap:

```bash
sudo snap install ngrok
```

### Autentikasi:

```bash
ngrok config add-authtoken <YOUR_AUTHTOKEN>
```

Get your authtoken from: https://dashboard.ngrok.com/get-started/your-authtoken

### Verifikasi Instalasi:

```bash
ngrok --version
```

---

## 🔐 Setup ngrok Static URL

### 1. Reserve Static Domain (Berbayar)

Untuk mendapatkan static URL seperti `toad-current-humbly.ngrok-free.app`:

1. Login ke [ngrok dashboard](https://dashboard.ngrok.com/)
2. Upgrade ke plan berbayar (minimum $8/month)
3. Reserve domain di bagian "Domains"
4. Gunakan domain tersebut dalam script

### 2. Update Script

Edit file [`ngrok-static.sh`](../ngrok-static.sh):

```bash
# Ganti dengan static URL Anda
STATIC_URL="your-reserved-domain.ngrok-free.app"
```

---

## 🚀 Cara Menggunakan

### Step 1: Jalankan Laravel Server

```bash
php artisan serve --port=8000
```

### Step 2: Jalankan ngrok (Terminal Baru)

**Dengan Static URL (Berbayar):**
```bash
./ngrok-static.sh 8000
```

**Tanpa Static URL (Gratis):**
```bash
ngrok http 8000
```

### Step 3: Get Webhook URL

**Untuk Static URL:**
URL tetap: `https://your-domain.ngrok-free.app/webhook/whatsapp`

**Untuk Dynamic URL:**
Jalankan:
```bash
./update_ngrok.sh
```

Script akan menampilkan URL webhook yang perlu diset di Fonnte.

### Step 4: Update Webhook di Fonnte

1. Login ke [dashboard Fonnte](https://api.fonnte.com/)
2. Pilih device WhatsApp Anda
3. Update webhook URL
4. Save dan test

---

## 🧪 Testing

### Test Manual:

```bash
# GET request
curl https://your-domain.ngrok-free.app/webhook/whatsapp

# POST request
curl -X POST https://your-domain.ngrok-free.app/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

### Test dengan Script:

```bash
./test-webhook.sh https://your-domain.ngrok-free.app/webhook/whatsapp
```

### ngrok Inspector:

Akses http://localhost:4040 untuk:
- Melihat semua request masuk
- Replay request
- Inspect request/response
- Debug webhook

---

## 🔍 Troubleshooting

### 1. ngrok Tidak Ditemukan

**Error:**
```
bash: ngrok: command not found
```

**Solusi:**
```bash
# Ubuntu/Debian
sudo apt install ngrok

# Arch Linux
yay -S ngrok

# Snap
sudo snap install ngrok
```

### 2. Authentication Required

**Error:**
```
ERR_NGROK_108: You must sign up for ngrok and install your authtoken
```

**Solusi:**
```bash
ngrok config add-authtoken <YOUR_AUTHTOKEN>
```

Get token dari: https://dashboard.ngrok.com/get-started/your-authtoken

### 3. URL Static Tidak Bisa Diakses

**Error:**
```
ERR_NGROK_326: The domain you tried to use is not configured
```

**Solusi:**
- Pastikan domain sudah di-reserve di ngrok dashboard
- Cek kuota ngrok (free tier terbatas)
- Pastikan subscription aktif untuk static URL
- Restart ngrok: `./ngrok-static.sh 8000`

### 4. Port 8000 Sudah Digunakan

**Error:**
```
Failed to listen on localhost:8000
```

**Solusi:**
```bash
# Cek proses yang menggunakan port
lsof -i :8000

# Bunuh proses jika perlu
kill -9 <PID>

# Atau gunakan port lain
php artisan serve --port=8001
./ngrok-static.sh 8001
```

### 5. Webhook Tidak Menerima Request

**Cek Laravel server:**
```bash
curl http://localhost:8000/webhook/whatsapp
```

**Cek ngrok tunnel:**
```bash
curl http://localhost:4040/api/tunnels
```

**Cek route:**
```bash
php artisan route:list | grep webhook
```

### 6. Tunnel Disconnect Terus

**Penyebab:**
- Koneksi internet tidak stabil
- ngrok session timeout (free tier: 8 hours)

**Solusi:**
- Restart ngrok
- Upgrade ke plan berbayar untuk session unlimited
- Gunakan Cloudflare Tunnel sebagai alternatif

### 7. Error 502 Bad Gateway

**Penyebab:**
- Laravel server tidak running
- Port salah

**Solusi:**
```bash
# Pastikan server running
php artisan serve --port=8000

# Restart ngrok dengan port yang benar
./ngrok-static.sh 8000
```

---

## 📊 Monitoring

### ngrok Web Interface:

Akses: http://localhost:4040

Features:
- Request history
- Request/response inspection
- Replay requests
- Traffic statistics

### Laravel Logs:

```bash
tail -f storage/logs/laravel.log
```

### Test Endpoint:

```bash
# Check status
curl -I https://your-domain.ngrok-free.app/webhook/whatsapp

# Test with data
./test-webhook.sh https://your-domain.ngrok-free.app/webhook/whatsapp
```

---

## 💰 ngrok Pricing

### Free Plan:
- ❌ Dynamic URL (berubah setiap restart)
- ✅ 1 online ngrok process
- ✅ 40 connections/minute
- ⏰ 8 hour session timeout

### Personal Plan ($8/month):
- ✅ 1 static domain
- ✅ 3 online ngrok processes
- ✅ 120 connections/minute
- ✅ Unlimited session

### Pro Plan ($20/month):
- ✅ 3 static domains
- ✅ 10 online ngrok processes
- ✅ 240 connections/minute
- ✅ IP whitelisting

> **💡 Tip:** Untuk development, gunakan Cloudflare Tunnel (gratis selamanya) sebagai alternatif static URL

---

## 🎯 Quick Commands

### Start Development:
```bash
# Terminal 1: Laravel Server
php artisan serve --port=8000

# Terminal 2: ngrok
./ngrok-static.sh 8000

# Terminal 3: Update webhook (optional)
./update_ngrok.sh
```

### Scripts Yang Tersedia:
- `./ngrok-static.sh 8000` - Jalankan ngrok dengan static URL
- `./update_ngrok.sh` - Update .env dengan webhook URL (untuk dynamic URL)
- `./test-webhook.sh <url>` - Test webhook

---

## 🔗 Resources

- [ngrok Documentation](https://ngrok.com/docs)
- [ngrok Dashboard](https://dashboard.ngrok.com/)
- [ngrok Pricing](https://ngrok.com/pricing)
- [Laravel Trusted Proxies](https://laravel.com/docs/requests#configuring-trusted-proxies)

---

## ⚖️ ngrok vs Cloudflare Tunnel

| Feature | ngrok | Cloudflare Tunnel |
|---------|-------|-------------------|
| **Static URL** | Berbayar ($8/mo) | Gratis ✅ |
| **Setup** | Medium | Simple ✅ |
| **Inspector UI** | Yes ✅ | No |
| **Session Limit** | 8 hours (free) | Unlimited ✅ |
| **Command** | Panjang | Pendek ✅ |
| **Stability** | Good | Excellent ✅ |
| **Best For** | Testing & debugging | Production-like dev ✅ |

---

## 🤝 Need Help?

Jika mengalami masalah:

1. Cek troubleshooting section di atas
2. Test dengan `./test-webhook.sh`
3. Cek ngrok inspector: http://localhost:4040
4. Cek Laravel logs: `tail -f storage/logs/laravel.log`

Atau coba [Cloudflare Tunnel](QUICK-START-CLOUDFLARE.md) sebagai alternatif!

---

**Happy Coding! 🚀**
