#!/bin/bash

# Script untuk memperbarui NGROK_WEBHOOK_URL dalam file .env
# Jalankan ini setelah memulai ngrok: ./update_ngrok.sh

# Mendapatkan URL publik ngrok dari API lokal
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | jq -r '.tunnels[0].public_url')

if [ -z "$NGROK_URL" ]; then
    echo "Error: Tidak dapat mendapatkan URL ngrok. Pastikan ngrok berjalan di port 8000."
    exit 1
fi

# Hapus https:// jika ada, karena kita hanya ingin domain saja
NGROK_DOMAIN=$(echo $NGROK_URL | sed 's|https://||')

# Perbarui file .env
if [ -f .env ]; then
    sed -i "s|NGROK_WEBHOOK_URL=.*|NGROK_WEBHOOK_URL=$NGROK_DOMAIN|" .env
    sed -i "s|APP_URL=.*|APP_URL=$NGROK_URL|" .env
    echo "NGROK_WEBHOOK_URL diperbarui ke $NGROK_DOMAIN"
    echo "APP_URL diperbarui ke $NGROK_URL"
    echo "Set webhook di dashboard Fonnte ke: $NGROK_URL/webhook/whatsapp"
    echo ""
    echo "Bersihkan cache konfigurasi:"
    echo "php artisan config:clear"
else
    echo "Error: File .env tidak ditemukan"
    exit 1
fi
