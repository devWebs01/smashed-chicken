#!/bin/bash

# Script untuk menjalankan Cloudflare Tunnel
# Domain static untuk project ini
TUNNEL_DOMAIN="local.systemwebsite.my.id"

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Cek apakah cloudflared tersedia
if ! command -v cloudflared &> /dev/null; then
    echo -e "${RED}❌ cloudflared tidak ditemukan.${NC}"
    echo ""
    echo "Install cloudflared terlebih dahulu:"
    echo "  Ubuntu/Debian: wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb && sudo dpkg -i cloudflared-linux-amd64.deb"
    echo "  Arch Linux: yay -S cloudflared-bin"
    echo ""
    echo "Atau kunjungi: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/"
    exit 1
fi

# Cek apakah port number disediakan
if [ $# -eq 0 ]; then
    PORT=8000
else
    PORT=$1
fi

echo -e "${GREEN}🚀 Menjalankan Cloudflare Tunnel${NC}"
echo -e "${BLUE}🌐 Domain: https://$TUNNEL_DOMAIN${NC}"
echo -e "${BLUE}🔌 Port: $PORT${NC}"
echo ""
echo -e "${GREEN}📋 Webhook URL untuk Fonnte:${NC}"
echo -e "${BLUE}   https://$TUNNEL_DOMAIN/webhook/whatsapp${NC}"
echo ""
echo -e "${GREEN}💡 Tips:${NC}"
echo "   - Pastikan Laravel server berjalan di port $PORT"
echo "   - Jalankan: php artisan serve --port=$PORT"
echo "   - Update webhook URL di dashboard Fonnte"
echo ""

# Jalankan cloudflared tunnel
cloudflared tunnel --url http://localhost:$PORT
