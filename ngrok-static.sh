#!/bin/bash

# Script untuk menjalankan ngrok dengan URL static
# URL static untuk project ini
STATIC_URL="toad-current-humbly.ngrok-free.app"

# Cek apakah ngrok tersedia
if ! command -v ngrok &> /dev/null; then
    echo "❌ ngrok tidak ditemukan. Pastikan ngrok sudah terinstall."
    exit 1
fi

# Cek apakah port number disediakan
if [ $# -eq 0 ]; then
    echo "📝 Penggunaan: ./ngrok-static.sh <port>"
    echo "📝 Contoh: ./ngrok-static.sh 8000"
    exit 1
fi

PORT=$1
echo "🚀 Menjalankan ngrok dengan URL static: https://$STATIC_URL"
echo "🌐 Port: $PORT"
echo ""

# Jalankan ngrok dengan URL static
ngrok http --url=$STATIC_URL $PORT
