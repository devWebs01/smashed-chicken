# smashed-chicken

Proyek ini mencakup sistem pemesanan WhatsApp menggunakan API Fonnte.

## Pengaturan Integrasi WhatsApp

### Prasyarat
- Aplikasi Laravel
- Akun Fonnte (https://fonnte.com)
- ngrok untuk pengembangan lokal

### Instalasi
1. Klon repositori
2. Salin `.env.example` ke `.env`
3. Atur `ACCOUNT_TOKEN` dari dashboard Fonnte
4. Jalankan `composer install` dan `npm install`
5. Jalankan perintah setup: `php artisan whatsapp:setup`

### Pengembangan Lokal dengan ngrok
1. Instal ngrok: `snap install ngrok`
2. Autentikasi ngrok: `ngrok config add-authtoken TOKEN_NGROK_ANDA`
3. Mulai Laravel: `php artisan serve`
4. Di terminal lain: `ngrok http 8000`
5. Jalankan skrip: `./update_ngrok.sh` (memperbarui .env dengan URL ngrok)
6. Atur webhook di dashboard Fonnte: `https://{ngrok-url}/webhook/whatsapp`

### Penggunaan
- Kirim pesan "menu" untuk melihat produk
- Kirim nomor produk untuk memesan (misalnya, "1 2" untuk 2 porsi produk 1)
- Ikuti petunjuk untuk pengiriman dan pembayaran