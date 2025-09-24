#!/bin/bash

# Script to update NGROK_WEBHOOK_URL in .env file
# Run this after starting ngrok: ./update_ngrok.sh

# Get ngrok public URL from local API
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | jq -r '.tunnels[0].public_url')

if [ -z "$NGROK_URL" ]; then
    echo "Error: Could not get ngrok URL. Make sure ngrok is running on port 8000."
    exit 1
fi

# Remove https:// if present, since we want the domain only
NGROK_DOMAIN=$(echo $NGROK_URL | sed 's|https://||')

# Update .env file
if [ -f .env ]; then
    sed -i "s|NGROK_WEBHOOK_URL=.*|NGROK_WEBHOOK_URL=$NGROK_DOMAIN|" .env
    echo "Updated NGROK_WEBHOOK_URL to $NGROK_DOMAIN"
    echo "Set webhook in Fonnte dashboard to: https://$NGROK_DOMAIN/webhook/whatsapp"
else
    echo "Error: .env file not found"
    exit 1
fi
