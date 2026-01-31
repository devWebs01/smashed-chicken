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

- **[RUNNING-APP.md](RUNNING-APP.md)** 🆕

  Panduan menjalankan aplikasi:
  - Development normal (tanpa Docker)
  - Development dengan Docker Sail
  - Switching antar mode
  - Troubleshooting database issues

---

### 📖 API & Testing

- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)**

  Dokumentasi API untuk integrasi Fonnte:
  - Webhook endpoints
  - Fonnte API methods
  - Request/response examples

- **[TESTING_DOCS.md](TESTING_DOCS.md)**

  Panduan testing webhook:
  - Test script usage
  - Manual testing
  - Debugging tips

- **[TROUBLESHOOTING_WEBHOOK.md](TROUBLESHOOTING_WEBHOOK.md)**

  Troubleshooting khusus webhook:
  - Common issues
  - Solutions
  - Debug techniques

---

### 🔄 Update & Changelog

- **[FONNTE_UPDATE_PLAN.md](FONNTE_UPDATE_PLAN.md)** ✅ **ALL PHASES COMPLETED**

  Update Fonnte 12 Januari 2026:
  - ✅ Phase 1: Critical Updates (Webhook POST only, HTTP 200, Quote reply)
  - ✅ Phase 2: Enhanced Features (Typing API, Media support)
  - ✅ Phase 3: Optional Enhancements (Message Logs, Slack Notifications, Statistics Command)

- **[CHANGELOG-CLOUDFLARE-TUNNEL.md](CHANGELOG-CLOUDFLARE-TUNNEL.md)**

  Changelog implementasi Cloudflare Tunnel:
  - Daftar perubahan file
  - Migration guide
  - Perbandingan sebelum/sesudah

---

## 🎯 Mulai dari Mana?

### Untuk Pemula atau Development Baru:
1. Baca [QUICK-START-CLOUDFLARE.md](QUICK-START-CLOUDFLARE.md)
2. Setup dalam 3 langkah
3. Langsung bisa development!

### Untuk Troubleshooting:
1. Cek [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md#troubleshooting)
2. Cek [TROUBLESHOOTING_WEBHOOK.md](TROUBLESHOOTING_WEBHOOK.md)
3. Gunakan script `test-real-fonnte.sh` untuk debugging
4. Cek Laravel logs: `tail -f storage/logs/laravel.log`

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
./scripts_test/test-real-fonnte.sh
```

Webhook URL: `https://local.systemwebsite.my.id/webhook/whatsapp`

📚 Panduan: [QUICK-START-CLOUDFLARE.md](QUICK-START-CLOUDFLARE.md)

---

### 🐛 Debugging Webhook

**Script Testing:**
```bash
./scripts_test/test-real-fonnte.sh
```

Script ini akan:
- ✅ Test basic message
- ✅ Test dengan field name
- ✅ Test order message
- ✅ Test reply handling
- ✅ Test error handling
- ✅ Tampilkan hasil semua test

**Manual Testing:**
```bash
# Test endpoint
curl -X POST https://local.systemwebsite.my.id/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"sender":"6281234567890","message":"test","device":"6281234567890"}'

# Cek logs
tail -f storage/logs/laravel.log
```

---

## 🔧 Tools & Scripts

### Scripts yang Tersedia:

| Script | Fungsi | Dokumentasi |
|--------|--------|-------------|
| `cloudflare-tunnel.sh` | Jalankan Cloudflare Tunnel | [CLOUDFLARE-TUNNEL-SETUP.md](CLOUDFLARE-TUNNEL-SETUP.md) |
| `test-real-fonnte.sh` | Test webhook Fonnte | [TESTING_DOCS.md](TESTING_DOCS.md) |

### Laravel Commands:

| Command | Fungsi |
|---------|--------|
| `php artisan whatsapp:setup` | Setup awal project |
| `php artisan serve --port=8000` | Jalankan Laravel server |
| `php artisan route:list` | Lihat semua routes |
| `php artisan config:clear` | Clear config cache |
| `php artisan cache:clear` | Clear application cache |

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

---

## 🆘 Troubleshooting Cepat

### Webhook tidak bekerja?

```bash
# 1. Test webhook
./scripts_test/test-real-fonnte.sh

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

📚 Troubleshooting lengkap: [TROUBLESHOOTING_WEBHOOK.md](TROUBLESHOOTING_WEBHOOK.md)

---

## 🔗 Links & Resources

### External Resources:
- [Fonnte Documentation](https://docs.fonnte.com/)
- [Fonnte Update 12 Jan 2026](https://docs.fonnte.com/update-12-januari-2026/)
- [Cloudflare Tunnel Docs](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [Laravel Docs](https://laravel.com/docs)
- [Filament Docs](https://filamentphp.com/docs)

### Project Resources:
- [Main README](../README.md)
- [Environment Example](../.env.example)
- [Routes](../routes/web.php)
- [WhatsApp Controller](../app/Http/Controllers/WhatsAppController.php)
- [Fonnte Service](../app/Services/FonnteService.php)

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
- ✅ [Update Fonnte 2026](FONNTE_UPDATE_PLAN.md) - Implementasi completed
- 📋 [API Documentation](API_DOCUMENTATION.md) - Referensi API
- 🏠 [Back to Main README](../README.md) - Dokumentasi utama project

---

**Happy Coding! 🚀**

*Last updated: January 31, 2026*
