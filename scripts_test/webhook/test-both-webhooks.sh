#!/bin/bash

# Script untuk test kedua webhook: WhatsApp dan Auto-Reply
# Verifikasi kedua endpoint berfungsi dengan benar

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base URL
BASE_URL="https://local.systemwebsite.my.id"

echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}🧪 Testing Both Webhooks${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

# Clear cache
echo -e "${YELLOW}🧹 Clearing cache...${NC}"
php artisan cache:clear > /dev/null 2>&1
sleep 1
echo ""

# Function to test webhook
test_webhook() {
    local webhook_name=$1
    local webhook_path=$2
    local test_data=$3

    echo -e "${BLUE}📡 Testing $webhook_name Webhook${NC}"
    echo -e "${BLUE}   URL: $BASE_URL$webhook_path${NC}"
    echo ""

    # Test 1: Check if endpoint exists
    echo -e "${YELLOW}   Test 1: Endpoint Availability${NC}"
    # Use OPTIONS to check allowed methods
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X OPTIONS "$BASE_URL$webhook_path" 2>/dev/null)
    if [ "$RESPONSE" = "405" ]; then
        # Try with POST for endpoints that only accept POST
        RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL$webhook_path" \
            -H "Content-Type: application/json" \
            -d '{"test": "check"}' 2>/dev/null)
        if [ "$RESPONSE" = "200" ] || [ "$RESPONSE" = "422" ]; then
            echo -e "${GREEN}   ✅ Endpoint Available (POST only)${NC}"
        else
            echo -e "${RED}   ❌ Endpoint Error (HTTP $RESPONSE)${NC}"
        fi
    elif [ "$RESPONSE" = "200" ]; then
        echo -e "${GREEN}   ✅ Endpoint Available (HTTP $RESPONSE)${NC}"
    else
        echo -e "${RED}   ❌ Endpoint Error (HTTP $RESPONSE)${NC}"
    fi
    echo ""

    # Test 2: POST request with test data
    echo -e "${YELLOW}   Test 2: POST Request${NC}"
    echo "   Command: curl -X POST $BASE_URL$webhook_path -H 'Content-Type: application/json' -d '$test_data'"
    echo ""

    RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "$BASE_URL$webhook_path" \
        -H "Content-Type: application/json" \
        -d "$test_data")

    HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE" | cut -d':' -f2)
    BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE/d')

    echo "   Response Body:"
    echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
    echo ""

    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}   ✅ POST Request: Success (HTTP $HTTP_CODE)${NC}"

        # Check if response contains status
        if echo "$BODY" | grep -q '"status":"success"'; then
            echo -e "${GREEN}   ✅ Auto-reply Sent${NC}"
        elif echo "$BODY" | grep -q '"status":"ok"'; then
            echo -e "${GREEN}   ✅ WhatsApp Processed${NC}"
        else
            echo -e "${YELLOW}   ⚠️  No status in response${NC}"
        fi
    else
        echo -e "${RED}   ❌ POST Request: Failed (HTTP $HTTP_CODE)${NC}"
    fi
    echo ""
}

# Test 1: WhatsApp Webhook
WHATSAPP_DATA='{
  "device": "6285951572182",
  "sender": "628978301766",
  "message": "Halo, saya mau order",
  "member": {
    "jid": "628978301766@s.whatsapp.net",
    "name": "Test Customer"
  }
}'
test_webhook "WhatsApp" "/webhook/whatsapp" "$WHATSAPP_DATA"

# Test 2: Auto-Reply Webhook
AUTOREPLY_DATA='{
  "device": "6285951572182",
  "sender": "628978301766",
  "message": "menu"
}'
test_webhook "Auto-Reply" "/webhook/autoreply" "$AUTOREPLY_DATA"

# Test 3: Auto-Reply with different messages
echo -e "${BLUE}🤖 Testing Auto-Reply Variations${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo ""

messages=("hello" "help" "apa kabar?" "order" "")

for msg in "${messages[@]}"; do
    if [ -z "$msg" ]; then
        continue
    fi

    echo -e "${YELLOW}📨 Testing: '$'${msg}'${NC}"
    RESPONSE=$(curl -s -X POST "$BASE_URL/webhook/autoreply" \
        -H "Content-Type: application/json" \
        -d "{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"$msg\"}")

    if echo "$RESPONSE" | grep -q '"status":"success"'; then
        echo -e "${GREEN}   ✅ Success${NC}"
        # Extract reply message
        reply=$(echo "$RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin)['reply'])" 2>/dev/null)
        echo "   Reply: $reply"
    else
        echo -e "${RED}   ❌ Failed${NC}"
    fi
    echo ""
done

# Summary
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}🎉 Testing Complete!${NC}"
echo ""
echo -e "${BLUE}📝 Setup Instructions:${NC}"
echo "1. WhatsApp Webhook: Set to $BASE_URL/webhook/whatsapp"
echo "2. Auto-Reply Webhook: Set to $BASE_URL/webhook/autoreply"
echo ""
echo -e "${BLUE}🔧 Debug Commands:${NC}"
echo "• Check Laravel logs: tail -f storage/logs/laravel.log"
echo "• List routes: php artisan route:list | grep webhook"
echo "• Test with curl:"
echo "  curl -X POST $BASE_URL/webhook/whatsapp -H 'Content-Type: application/json' -d '{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"test\"}'"
echo "  curl -X POST $BASE_URL/webhook/autoreply -H 'Content-Type: application/json' -d '{\"device\":\"6285951572182\",\"sender\":\"628978301766\",\"message\":\"hello\"}'"
echo ""
echo -e "${YELLOW}💡 Notes:${NC}"
echo "• Both webhooks must return HTTP 200"
echo "• WhatsApp webhook handles order processing"
echo "• Auto-Reply webhook sends instant responses"
echo "• Check Fonnte dashboard for device status"
echo ""