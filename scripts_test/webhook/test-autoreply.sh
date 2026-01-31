#!/bin/bash

# Script untuk test Auto-Reply Webhook
# Mengirim berbagai jenis pesan dan memverifikasi balasan

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# URL webhook
if [ $# -eq 0 ]; then
    WEBHOOK_URL="https://local.systemwebsite.my.id/webhook/autoreply"
else
    WEBHOOK_URL=$1
fi

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🤖 Testing Auto-Reply Webhook${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📡 URL: $WEBHOOK_URL${NC}"
echo ""

# Clear cache
echo -e "${YELLOW}🧹 Clearing cache...${NC}"
php artisan cache:clear > /dev/null 2>&1
sleep 1
echo ""

echo -e "${BLUE}────────────────────────────────────────${NC}"

# Test 1: Greeting Message
echo -e "${YELLOW}📨 Test 1: Greeting (Halo)${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "Halo",
    "timestamp": "2026-01-31T12:00:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 2: Menu Request
echo -e "${YELLOW}📨 Test 2: Menu Request${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "menu",
    "timestamp": "2026-01-31T12:01:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 3: Help Request
echo -e "${YELLOW}📨 Test 3: Help Request${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "help",
    "timestamp": "2026-01-31T12:02:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 4: Random Message
echo -e "${YELLOW}📨 Test 4: Random Message${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "Apa kabar?",
    "timestamp": "2026-01-31T12:03:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 5: Order Inquiry
echo -e "${YELLOW}📨 Test 5: Order Inquiry${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "Saya mau order",
    "timestamp": "2026-01-31T12:04:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 6: Empty Message
echo -e "${YELLOW}📨 Test 6: Empty Message${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "",
    "timestamp": "2026-01-31T12:05:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

# Test 7: Long Message
echo -e "${YELLOW}📨 Test 7: Long Message${NC}"
echo -e "${YELLOW}────────────────────────────────────────${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "6285951572182",
    "sender": "628978301766",
    "message": "Halo saya ingin pesan ayam geprek original dengan level pedas sedikit, tambah keju, dan order untuk delivery ke alamat Jl. Sudirman No. 456, Jakarta Selatan. Berapa total harganya ya?",
    "timestamp": "2026-01-31T12:06:00Z"
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ All auto-reply tests completed!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${YELLOW}📊 Check logs untuk melihat hasil:${NC}"
echo "   - storage/logs/laravel.log"
echo "   - Cek WhatsApp untuk balasan dari bot"
echo ""
echo -e "${YELLOW}💡 Tips:${NC}"
echo "   - Pastikan Fonnte device aktif"
echo "   - Cek quota Fonnte"
echo "   - Verifikasi balasan diterima di WhatsApp"
echo ""