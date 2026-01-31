# Fonnte WhatsApp Webhook - Troubleshooting Guide

## Masalah: Bot Tidak Balas Pesan via WhatsApp

### Checklist Debugging

#### 1. **Verifikasi Webhook URL di Fonnte Dashboard**

> [!IMPORTANT]
> Ini penyebab paling umum! Pastikan webhook URL di Fonnte dashboard BENAR.

**Langkah:**
1. Login ke [Fonnte Dashboard](https://device.fonnte.com)
2. Pilih device Anda (`6285951572182`)
3. Klik **Settings** atau **Webhook Settings**
4. Pastikan URL webhook:
   ```
   https://local.systemwebsite.my.id/webhook/whatsapp
   ```
5. **Simpan** perubahan

**Screenshot lokasi:**
- Biasanya di menu: Device → Settings → Webhook URL
- Atau: Dashboard → Devices → [Device Name] → Advanced → Webhook

#### 2. **Test Koneksi Webhook**

```bash
# Test dari local
curl https://local.systemwebsite.my.id/webhook/whatsapp

# Harus return JSON response, bukan error
```

#### 3. **Simulate Real Fonnte Payload**

```bash
bash test-real-fonnte.sh
```

Lalu cek log:
```bash
tail -f storage/logs/laravel.log
```

**Expected log:**
```
[timestamp] local.INFO: WhatsApp Webhook Received {...}
[timestamp] local.INFO: Processing text message {...}
[timestamp] local.INFO: Fonnte API Response {...}
```

#### 4. **Cek Format Payload dari Fonnte**

Fonnte mengirim payload dengan struktur:
```json
{
  "device": "6285951572182",
  "sender": "628xxx",
  "message": "text message",
  "member": {
    "jid": "628xxx@s.whatsapp.net",
    "name": "Customer Name"
  },
  "messageTimestamp": 1234567890
}
```

Jika ada field tambahan yang tidak expected, mungkin perlu adjust validation.

#### 5. **Enable Debug Logging**

Sudah ada comprehensive logging di `WhatsAppController.php`:
- Line 18-22: Log semua incoming webhook
- Line 27: Log processing result
- Line 33-36: Log exceptions

Untuk melihat real-time:
```bash
tail -f storage/logs/laravel.log | grep "WhatsApp"
```

#### 6. **Common Issues**

**Issue: Tidak ada log sama sekali**
- ❌ Webhook URL salah di Fonnte
- ❌ CloudFlare tunnel mati
- ❌ Laravel server tidak running

**Solution:**
```bash
# Restart server
php artisan serve

# Restart tunnel
bash cloudflare-tunnel.sh

# Test URL
curl https://local.systemwebsite.my.id/webhook/whatsapp
```

**Issue: Log ada tapi tidak ada response ke customer**
- ❌ Fonnte device disconnected
- ❌ Fonnte quota habis
- ❌ Token salah

**Solution:**
- Cek Fonnte dashboard → Device status = `CONNECTED`
- Cek quota di dashboard
- Verify token di `.env` match dengan Fonnte

**Issue: Log error "Missing sender or device phone"**
- ❌ Payload structure berbeda dari expected

**Solution:**
- Capture real payload dengan `test-real-fonnte.sh`
- Compare dengan payload di log
- Adjust validation if needed

#### 7. **Test dengan Send Message dari WhatsApp**

1. Pastikan webhook URL sudah benar di Fonnte
2. Send message dari HP Anda ke nomor bot: `6285951572182`
3. Lihat log real-time:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. Jika ada log → Webhook working! Cek response logic
5. Jika TIDAK ada log → Webhook URL salah atau tunnel mati

## Quick Fix Commands

```bash
# 1. Clear all cache
php artisan cache:clear
php artisan config:clear

# 2. Restart services
php artisan serve  # Terminal 1
bash cloudflare-tunnel.sh  # Terminal 2

# 3. Test webhook
bash test-real-fonnte.sh

# 4. Watch logs
tail -f storage/logs/laravel.log
```

## Fonnte Webhook Configuration

**Correct** URL format:
```
https://local.systemwebsite.my.id/webhook/whatsapp
```

**Incorrect** URLs:
- ❌ `http://local.systemwebsite.my.id/webhook/whatsapp` (http instead of https)
- ❌ `https://local.systemwebsite.my.id/api/webhook/whatsapp` (/api prefix)
- ❌ `https://127.0.0.1:8000/webhook/whatsapp` (localhost not accessible by Fonnte)

## Next Steps

1. **Verify Fonnte webhook URL**
2. **Send test message dari WhatsApp**
3. **Check logs** untuk lihat apakah webhook diterima
4. **If no logs**: Fix webhook URL atau tunnel
5. **If logs exist**: Debug response logic

Jika masih stuck, share log output untuk analysis lebih lanjut.
