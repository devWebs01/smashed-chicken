#!/bin/bash

# Script untuk test webhook WhatsApp
# Gunakan ini untuk memverifikasi webhook berfungsi dengan benar

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# URL webhook (bisa dari env atau parameter)
if [ $# -eq 0 ]; then
    WEBHOOK_URL="https://local.systemwebsite.my.id/webhook/whatsapp"
else
    WEBHOOK_URL=$1
fi

echo -e "${BLUE}🧪 Testing Webhook${NC}"
echo -e "${BLUE}📡 URL: $WEBHOOK_URL${NC}"
echo ""

# Test 1: GET Request
echo -e "${YELLOW}Test 1: GET Request${NC}"
echo "Command: curl -X GET $WEBHOOK_URL"
echo ""
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$WEBHOOK_URL")
if [ "$RESPONSE" = "200" ]; then
    echo -e "${GREEN}✅ GET Request: Success (HTTP $RESPONSE)${NC}"
else
    echo -e "${RED}❌ GET Request: Failed (HTTP $RESPONSE)${NC}"
fi
echo ""

# Test 2: POST Request dengan data minimal
echo -e "${YELLOW}Test 2: POST Request (Test Data)${NC}"
echo "Command: curl -X POST $WEBHOOK_URL -H 'Content-Type: application/json' -d '{\"test\": \"data\"}'"
echo ""
RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "$WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -d '{"test": "data"}')

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Body:"
echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "201" ]; then
    echo -e "${GREEN}✅ POST Request: Success (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ POST Request: Failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

# Test 3: POST Request dengan simulasi webhook Fonnte
echo -e "${YELLOW}Test 3: POST Request (Simulasi Webhook Fonnte)${NC}"
TEST_DATA='{
  "device": "6285951572182",
  "sender": "628978301766",
  "message": "menu",
  "member": {
    "jid": "628978301766@s.whatsapp.net",
    "name": "Test User"
  }
}'

echo "Command: curl -X POST $WEBHOOK_URL -H 'Content-Type: application/json' -d '$TEST_DATA'"
echo ""

RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "$WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -d "$TEST_DATA")

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

echo "Response Body:"
echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
echo ""

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "201" ]; then
    echo -e "${GREEN}✅ Fonnte Simulation: Success (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ Fonnte Simulation: Failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

# Summary
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}🎉 Testing Selesai!${NC}"
echo ""
echo -e "${BLUE}📝 Langkah Selanjutnya:${NC}"
echo "1. Pastikan semua test di atas berhasil (✅)"
echo "2. Update webhook URL di dashboard Fonnte:"
echo "   $WEBHOOK_URL"
echo "3. Test dengan mengirim pesan WhatsApp ke nomor bot"
echo ""
echo -e "${YELLOW}💡 Tips Debugging:${NC}"
echo "• Cek log Laravel: tail -f storage/logs/laravel.log"
echo "• Cek route: php artisan route:list | grep webhook"
echo "• Test localhost: curl http://localhost:8000/webhook/whatsapp"
echo ""
