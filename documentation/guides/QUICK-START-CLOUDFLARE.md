# ⚡ Quick Start: Cloudflare Tunnel untuk Webhook

Setup webhook WhatsApp dengan Cloudflare Tunnel dalam 3 langkah mudah!

## 🎯 Kenapa Cloudflare Tunnel?

✅ URL static: `local.testingbae0000.my.id` (gampang diingat!)  
✅ Gratis selamanya (tidak seperti ngrok berbayar)  
✅ Tidak perlu command panjang berulang kali  

❌ ngrok command yang ribet:
```bash
ngrok http --url=toad-current-humbly.ngrok-free.app 8000
```

## 🚀 Cara Pakai (3 Langkah)

### 1️⃣ Jalankan Laravel Server

```bash
php artisan serve --port=8000
```

### 2️⃣ Jalankan Cloudflare Tunnel (Terminal Baru)

```bash
./cloudflare-tunnel.sh 8000
```

### 3️⃣ Update Webhook di Fonnte

Set webhook URL ke:
```
https://local.testingbae0000.my.id/webhook/whatsapp
```

## ✅ Test Webhook

```bash
./test-webhook.sh
```

## 🔧 Sudah Dikonfigurasi Otomatis

✅ `.env` - APP_URL sudah diupdate  
✅ `bootstrap/app.php` - Trusted proxies sudah ditambahkan  
✅ `routes/web.php` - Route webhook sudah ada  
✅ CSRF protection - Webhook sudah di-exclude  

## 📚 Dokumentasi Lengkap

Baca [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md) untuk:
- Instalasi cloudflared
- Troubleshooting detail
- Tips & tricks
- Monitoring logs

## 🆘 Troubleshooting Cepat

**Webhook tidak bekerja?**

1. Cek Laravel server berjalan:
   ```bash
   curl http://localhost:8000/webhook/whatsapp
   ```

2. Cek route terdaftar:
   ```bash
   php artisan route:list | grep webhook
   ```

3. Cek logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

**Cloudflared tidak ditemukan?**

Install dulu:
```bash
# Ubuntu/Debian
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb
```

## 🎉 Selesai!

Sekarang webhook Anda sudah berjalan di:
```
https://local.testingbae0000.my.id/webhook/whatsapp
```

**Happy Coding! 🚀**
