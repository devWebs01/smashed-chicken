# 📚 Dokumentasi Sistem Pemesanan Ayam Geprek

Selamat datang di dokumentasi lengkap sistem pemesanan via WhatsApp!

## 📂 Struktur Dokumentasi

### 🚀 Quick Start & Setup

- **[QUICK-START-CLOUDFLARE.md](QUICK-START-CLOUDFLARE.md)** ⭐
  
  Setup webhook dengan Cloudflare Tunnel dalam 3 langkah mudah. **Recommended untuk semua user!**
  
  ✅ Gratis selamanya
  ✅ URL static yang pendek
  ✅ Command simple

- **[CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md)**
  
  Panduan lengkap Cloudflare Tunnel dengan:
  - Instalasi cloudflared
  - Konfigurasi detail
  - Troubleshooting lengkap
  - Tips & tricks

- **[NGROK-SETUP.md](NGROK-SETUP.md)**
  
  Setup webhook dengan ngrok (alternatif):
  - Instalasi ngrok
  - Setup static URL (berbayar)
  - Perbandingan dengan Cloudflare Tunnel
  - Troubleshooting ngrok

- **[CHANGELOG-CLOUDFLARE-TUNNEL.md](CHANGELOG-CLOUDFLARE-TUNNEL.md)**
  
  Changelog implementasi Cloudflare Tunnel:
  - Daftar perubahan file
  - Migration guide
  - Perbandingan sebelum/sesudah
  - Summary implementasi

---

## 🎯 Mulai dari Mana?

### Untuk Pemula atau Development Baru:
1. Baca [QUICK-START-CLOUDFLARE.md](QUICK-START-CLOUDFLARE.md)
2. Setup dalam 3 langkah
3. Langsung bisa development!

### Untuk Troubleshooting:
1. Cek [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#troubleshooting)
2. Gunakan script `test-webhook.sh` untuk debugging
3. Cek Laravel logs: `tail -f storage/logs/laravel.log`

### Untuk User ngrok Existing:
1. Baca [NGROK-SETUP.md](NGROK-SETUP.md)
2. Atau migrate ke Cloudflare Tunnel (lebih mudah!)

---

## 📖 Panduan Berdasarkan Use Case

### 💻 Development Lokal

**Recommended: Cloudflare Tunnel**

```bash
# Terminal 1
php artisan serve --port=8000

# Terminal 2
./cloudflare-tunnel.sh 8000

# Terminal 3 (optional)
./test-webhook.sh
```

Webhook URL: `https://local.testingbae0000.my.id/webhook/whatsapp`

📚 Panduan: [QUICK-START-CLOUDFLARE.md](QUICK-START-CLOUDFLARE.md)

---

### 🔄 Ganti dari ngrok ke Cloudflare Tunnel

**Keuntungan:**
- ✅ Tidak perlu command panjang lagi
- ✅ URL static gratis selamanya
- ✅ Lebih stabil untuk webhook

**Langkah Migrasi:**
1. Install cloudflared (lihat [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#instalasi-cloudflared))
2. Jalankan `./cloudflare-tunnel.sh 8000`
3. Update webhook URL di Fonnte dashboard
4. Done! 🎉

---

### 🐛 Debugging Webhook

**Script Testing:**
```bash
./test-webhook.sh
```

Script ini akan:
- ✅ Test GET request
- ✅ Test POST request
- ✅ Test simulasi webhook Fonnte
- ✅ Tampilkan hasil semua test

**Manual Testing:**
```bash
# Test endpoint
curl https://local.testingbae0000.my.id/webhook/whatsapp

# Test dengan data
curl -X POST https://local.testingbae0000.my.id/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

**Cek Logs:**
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Route check
php artisan route:list | grep webhook
```

📚 Troubleshooting lengkap: [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#troubleshooting)

---

## 🎓 Tutorial & Tips

### Setup Cloudflare Tunnel di Laptop/PC Baru

```bash
# 1. Clone project
git clone <repository>
cd geprek

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate
php artisan whatsapp:setup

# 4. Install cloudflared (jika belum)
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# 5. Run development
php artisan serve --port=8000
./cloudflare-tunnel.sh 8000
```

### Development dengan tmux/screen (Background)

```bash
# Dengan tmux
tmux new -s laravel
php artisan serve --port=8000
# Ctrl+B, D untuk detach

tmux new -s tunnel
./cloudflare-tunnel.sh 8000
# Ctrl+B, D untuk detach

# List sessions
tmux ls

# Attach kembali
tmux attach -t laravel
```

### Update Webhook URL di Multiple Device

Jika punya beberapa device WhatsApp:

1. Login ke [Fonnte dashboard](https://api.fonnte.com/)
2. Untuk setiap device:
   - Pilih device
   - Update webhook: `https://local.testingbae0000.my.id/webhook/whatsapp`
   - Save
3. Test dengan mengirim pesan ke masing-masing nomor

---

## 🔧 Tools & Scripts

### Scripts yang Tersedia:

| Script | Fungsi | Dokumentasi |
|--------|--------|-------------|
| `cloudflare-tunnel.sh` | Jalankan Cloudflare Tunnel | [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md) |
| `test-webhook.sh` | Test webhook otomatis | [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#testing-webhook) |
| `ngrok-static.sh` | Jalankan ngrok static | [NGROK-SETUP.md](NGROK-SETUP.md) |
| `update_ngrok.sh` | Update .env ngrok URL | [NGROK-SETUP.md](NGROK-SETUP.md) |

### Laravel Commands:

| Command | Fungsi |
|---------|--------|
| `php artisan whatsapp:setup` | Setup awal project |
| `php artisan serve --port=8000` | Jalankan Laravel server |
| `php artisan route:list` | Lihat semua routes |
| `php artisan config:clear` | Clear config cache |

---

## 📊 Comparison: Cloudflare Tunnel vs ngrok

| Feature | Cloudflare Tunnel | ngrok |
|---------|-------------------|-------|
| **Static URL** | ✅ Gratis | 💰 $8/month |
| **Command** | `./cloudflare-tunnel.sh 8000` | `ngrok http --url=...` |
| **Session Limit** | ✅ Unlimited | ⏰ 8 hours (free) |
| **Setup Difficulty** | ⭐⭐ Easy | ⭐⭐⭐ Medium |
| **Inspector UI** | ❌ No | ✅ Yes |
| **Best For** | Development | Debug & inspect |

**Rekomendasi:** Gunakan Cloudflare Tunnel untuk development sehari-hari! 🚀

---

## 🆘 Troubleshooting Cepat

### Webhook tidak bekerja?

```bash
# 1. Test webhook
./test-webhook.sh

# 2. Cek Laravel server
curl http://localhost:8000/webhook/whatsapp

# 3. Cek route
php artisan route:list | grep webhook

# 4. Cek logs
tail -f storage/logs/laravel.log
```

### Cloudflared tidak ditemukan?

```bash
# Ubuntu/Debian
wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# Verifikasi
cloudflared --version
```

### Port 8000 sudah digunakan?

```bash
# Cek process
lsof -i :8000

# Kill process
kill -9 <PID>

# Atau gunakan port lain
php artisan serve --port=8001
./cloudflare-tunnel.sh 8001
```

📚 Troubleshooting lengkap: [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#troubleshooting)

---

## 🔗 Links & Resources

### External Resources:
- [Cloudflare Tunnel Docs](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [ngrok Documentation](https://ngrok.com/docs)
- [Fonnte API Docs](https://api.fonnte.com/docs)
- [Laravel Docs](https://laravel.com/docs)
- [Filament Docs](https://filamentphp.com/docs)

### Project Resources:
- [Main README](../README.md)
- [Environment Example](../.env.example)
- [Routes](../routes/web.php)
- [WhatsApp Controller](../app/Http/Controllers/WhatsAppController.php)

---

## 📝 Kontribusi Dokumentasi

Menemukan error atau ingin menambahkan dokumentasi?

1. Fork repository
2. Buat perubahan di folder `documentation/`
3. Submit pull request
4. Dokumentasi akan direview dan dimerge

**Format Dokumentasi:**
- Gunakan Markdown
- Sertakan contoh code yang jelas
- Tambahkan emoji untuk readability
- Include troubleshooting jika relevan

---

## 🎉 Quick Links

- 🚀 [Quick Start Cloudflare](QUICK-START-CLOUDFLARE.md) - Mulai dalam 3 langkah!
- 📖 [Panduan Lengkap Cloudflare](CLOUDFLARE-TUNNEL-SETUP.md) - Dokumentasi detail
- 🔧 [Setup ngrok](NGROK-SETUP.md) - Alternatif tunneling
- 🏠 [Back to Main README](../README.md) - Dokumentasi utama project

---

**Happy Coding! 🚀**

*Last updated: October 2025*
