#!/bin/bash

# Script untuk test order flow lengkap
# Simulasi customer memesan geprek dari awal hingga konfirmasi

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# URL webhook
if [ $# -eq 0 ]; then
    WEBHOOK_URL="https://local.systemwebsite.my.id/webhook/whatsapp"
else
    WEBHOOK_URL=$1
fi

# Data customer dan device
CUSTOMER_PHONE="628978301766"
DEVICE_PHONE="6285951572182"
CUSTOMER_NAME="Test Customer"

echo -e "${BLUE}🛍️  Simulasi Order Flow Geprek${NC}"
echo -e "${BLUE}📡 URL: $WEBHOOK_URL${NC}"
echo ""

# Clear cache dulu
echo -e "${YELLOW}Clearing cache...${NC}"
php artisan cache:clear
sleep 1

# Step 1: Customer baru (welcome message)
echo -e "${YELLOW}Step 1: Customer Baru (Welcome)${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "Halo",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 2: Input Name
echo -e "${YELLOW}Step 2: Input Nama${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "Budi Santoso",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 3: Input Address
echo -e "${YELLOW}Step 3: Input Alamat${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "Jl. Raya Testing No. 123, Jakarta",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 4: Request Menu
echo -e "${YELLOW}Step 4: Request Menu${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "menu",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 5: Order Product (misal: 1=2, 2=1)
echo -e "${YELLOW}Step 5: Order Product (1=2, 2=1)${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "1=2, 2=1",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 6: Delivery Method
echo -e "${YELLOW}Step 6: Pilih Delivery Method (delivery)${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "delivery",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 7: Delivery Address
echo -e "${YELLOW}Step 7: Input Alamat Pengiriman${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "Jl. Sudirman No. 456, Jakarta Selatan (Kantor)",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 8: Payment Method
echo -e "${YELLOW}Step 8: Pilih Payment Method (cash)${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "cash",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 2

# Step 9: Final Confirmation
echo -e "${YELLOW}Step 9: Konfirmasi Order${NC}"
curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "device": "'"$DEVICE_PHONE"'",
    "sender": "'"$CUSTOMER_PHONE"'",
    "message": "yes",
    "member": {
      "jid": "'"$CUSTOMER_PHONE"'@s.whatsapp.net",
      "name": "'"$CUSTOMER_NAME"'"
    }
  }' > /dev/null
echo -e "${GREEN}✓ Sent${NC}"
sleep 1

echo ""
echo -e "${GREEN}✅ Order simulation completed!${NC}"
echo -e "${BLUE}📊 Check logs untuk melihat hasil:${NC}"
echo -e "   - storage/logs/laravel.log"
echo -e "   - Slack channel"
echo ""
echo -e "${YELLOW}💡 Tips:${NC}"
echo -e "   - Cek database untuk order baru"
echo -e "   - Verify Fonnte API mengirim pesan ke customer"
echo ""
