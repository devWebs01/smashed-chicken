#!/bin/bash

# Script untuk simulate REAL Fonnte webhook payload
# Berdasarkan dokumentasi Fonnte terbaru: https://docs.fonnte.com/
# Update 12 Januari 2026: https://docs.fonnte.com/update-12-januari-2026/

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m'

# URL webhook
if [ $# -eq 0 ]; then
    WEBHOOK_URL="https://local.systemwebsite.my.id/webhook/whatsapp"
else
    WEBHOOK_URL=$1
fi

echo -e "${CYAN}========================================${NC}"
echo -e "${BLUE}🔍 Testing dengan REAL Fonnte Payload${NC}"
echo -e "${BLUE}   (Update 12 Januari 2026)${NC}"
echo -e "${CYAN}========================================${NC}"
echo -e "${BLUE}📡 URL: $WEBHOOK_URL${NC}"
echo ""

# Clear cache
echo -e "${YELLOW}🧹 Clearing cache...${NC}"
php artisan cache:clear
sleep 1
echo ""

# Test 1: Basic text message
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}📨 Test 1: Basic Text Message${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "menu",
    "inboxid": "msg_001_basic_1234567890",
    "timestamp": "1738175400",
    "message_id": "3EB0XXXXXXXXXXXX",
    "type": "text"
  }' | jq '.'
echo -e "\n"

sleep 2

# Test 2: Message with new fields (name)
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}📨 Test 2: Message with Name Field${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "1",
    "inboxid": "msg_002_with_name_1234567891",
    "timestamp": "1738175401",
    "message_id": "3EB0XXXXXXXXXXXY",
    "name": "Test Customer",
    "type": "text"
  }' | jq '.'
echo -e "\n"

sleep 2

# Test 3: Order message (product selection)
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}📨 Test 3: Order Message (1=2, 2=1)${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "1=2",
    "inboxid": "msg_003_order_1234567892",
    "timestamp": "1738175402",
    "message_id": "3EB0XXXXXXXXXXXZ",
    "name": "Test Customer",
    "type": "text"
  }' | jq '.'
echo -e "\n"

sleep 2

# Test 4: Reply-to-message test (should trigger quote reply)
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}📨 Test 4: Reply Test (selesai)${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "selesai",
    "inboxid": "msg_004_reply_1234567893",
    "timestamp": "1738175403",
    "message_id": "3EB0XXXXXXXXXXXA",
    "name": "Test Customer",
    "type": "text"
  }' | jq '.'
echo -e "\n"

sleep 2

# Test 5: Reset command
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}📨 Test 5: Reset Command${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "reset",
    "inboxid": "msg_005_reset_1234567894",
    "timestamp": "1738175404",
    "message_id": "3EB0XXXXXXXXXXXB",
    "name": "Test Customer",
    "type": "text"
  }' | jq '.'
echo -e "\n"

sleep 2

# Test 6: Error handling - Missing sender
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${RED}📨 Test 6: Error Handling (Missing sender)${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "message": "test",
    "inboxid": "msg_006_error_1234567895",
    "timestamp": "1738175405",
    "type": "text"
  }' | jq '.'
echo -e "\n"

echo -e "${CYAN}────────────────────────────────────────${NC}"
echo -e "${GREEN}✅ All tests completed!${NC}"
echo -e "${CYAN}────────────────────────────────────────${NC}"
echo ""
echo -e "${YELLOW}📋 Cek log:${NC}"
echo "  tail -f storage/logs/laravel.log"
echo ""
echo -e "${YELLOW}🔍 Cek detail response:${NC}"
echo "  grep 'WhatsApp Webhook' storage/logs/laravel.log"
echo ""
echo -e "${YELLOW}⚠️  Jika tidak ada log:${NC}"
echo "  1. Cek server running: curl http://127.0.0.1:8000"
echo "  2. Cek tunnel running: curl $WEBHOOK_URL"
echo "  3. Cek route: php artisan route:list | grep webhook"
echo ""
echo -e "${YELLOW}💡 Info Update Fonnte 12 Jan 2026:${NC}"
echo "  - Webhook MUST return HTTP 200 (even on error)"
echo "  - 15 retries every minute (not immediately)"
echo "  - New fields: timestamp, inboxid for reply feature"
echo ""
