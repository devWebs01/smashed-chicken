#!/bin/bash

# Script untuk simulate REAL Fonnte webhook payload
# Berdasarkan dokumentasi Fonnte: https://docs.fonnte.com/webhook/

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# URL webhook
if [ $# -eq 0 ]; then
    WEBHOOK_URL="https://local.systemwebsite.my.id/webhook/whatsapp"
else
    WEBHOOK_URL=$1
fi

echo -e "${BLUE}🔍 Testing dengan REAL Fonnte Payload${NC}"
echo -e "${BLUE}📡 URL: $WEBHOOK_URL${NC}"
echo ""

# Clear cache
echo -e "${YELLOW}Clearing cache...${NC}"
php artisan cache:clear
sleep 1

# REAL Fonnte Payload Structure
# Berdasarkan dokumentasi Fonnte
echo -e "${YELLOW}Sending REAL Fonnte webhook payload...${NC}"
curl -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "Halo, saya mau pesan",
    "member": {
      "jid": "628978301766@s.whatsapp.net",
      "name": "Test Customer"
    },
    "location": null,
    "messageTimestamp": 1738175400
  }'

echo ""
echo ""
echo -e "${GREEN}✅ Test selesai!${NC}"
echo ""
echo -e "${YELLOW}Cek log:${NC}"
echo "  tail -f storage/logs/laravel.log"
echo ""
echo -e "${YELLOW}Jika tidak ada log:${NC}"
echo "  1. Cek server running: curl http://127.0.0.1:8000"
echo "  2. Cek tunnel running: curl $WEBHOOK_URL"
echo "  3. Cek route: php artisan route:list | grep webhook"
echo ""
