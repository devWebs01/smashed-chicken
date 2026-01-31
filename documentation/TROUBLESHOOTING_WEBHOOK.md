# Troubleshooting: Fonnte Webhook & Message Sending Issues

## Problem: Messages Not Delivered to Customers

### Symptoms
- Webhook receives messages correctly
- Fonnte API returns `"status": true` with `"detail": "success! message in queue"`
- Messages show `"process": "pending"` status
- Customers do NOT receive WhatsApp messages

### Root Cause: **Device Disconnected**

According to [Fonnte's Message Status Documentation](https://docs.fonnte.com/message-status/):

> **Pending**: the message haven't sent due to **device not connected** or failed to process. The message will be resend in the next minute

> **Expired**: the message will not be sent by system due to **1 day of disconnected device**. Manual resend is required if you still want to send the message.

### Verification Steps

#### 1. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

Look for this response pattern:
```json
{
  "detail": "success! message in queue",
  "process": "pending",  // ← Indicates device disconnected
  "status": true
}
```

#### 2. Check Device Connection Status
```bash
php artisan fonnte:device-status
```

Or check a specific device:
```bash
php artisan fonnte:device-status 6285951572182
```

Expected output when device is **connected**:
```
| Property | Value        |
|----------|--------------|
| Device   | 6285951572182 |
| Status   | connected    |
```

Expected output when device is **disconnected**:
```
❌ Device is DISCONNECTED
Messages will remain in PENDING status until device is reconnected
```

#### 3. Check Fonnte Dashboard
1. Login to [Fonnte Dashboard](https://fonnte.com)
2. Go to **Device** menu
3. Check the device status indicator

### Solutions

#### Solution 1: Reconnect Your WhatsApp Device (Required)

1. Open Fonnte Dashboard
2. Go to **Device** section
3. Find your device (6285951572182)
4. Click **Connect** or scan QR code to reconnect
5. Keep WhatsApp open on your phone

**Important**: The WhatsApp device must stay connected for messages to be sent.

#### Solution 2: Reduce Message Frequency

If you're sending multiple messages rapidly, consider adding delays:

```php
// In your webhook handler
sleep(1); // Add 1 second delay between messages
$this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
```

#### Solution 3: Monitor Message Status

After reconnecting the device, check if pending messages are sent:

1. Go to Fonnte Dashboard → **Message History**
2. Filter by status: **Pending**
3. Click **Resend** for expired messages

### Why Auto-Reply Works But Main Webhook Doesn't

Both webhooks use the **same** Fonnte API call and device token. The difference is:

| Aspect | AutoReplyController | WhatsAppWebhookService |
|--------|---------------------|------------------------|
| Messages per webhook | 1 | Multiple (3-5+) |
| Processing speed | Fast | Slower (multi-step) |
| Deduplication | None | Has cache-based dedup |

When the device is disconnected:
- **Auto-reply**: Sends 1 message, might get through during reconnection window
- **Main webhook**: Sends multiple messages rapidly, all get stuck in pending queue

### Common Misconceptions

**❌ "There's a code bug preventing message sending"**
- ✅ Code is working correctly (API returns `status: true`)
- ❌ Device is disconnected, so messages can't be sent

**❌ "Fonnte API is down"**
- ✅ Fonnte API is working (accepting and queuing messages)
- ❌ Your WhatsApp device needs to be connected

**❌ "Messages are being sent but customer doesn't receive them"**
- ✅ Messages are in **pending queue**, not sent yet
- ❌ Device connection is required for Fonnte to send queued messages

### Prevention: Keep Device Connected

1. **Use a dedicated phone/tablet** for WhatsApp Business API
2. **Keep WhatsApp app open** on the device
3. **Stable internet connection** required
4. **Regular reconnection** may be needed if connection drops

### Additional Resources

- [Fonnte Message Status Documentation](https://docs.fonnte.com/message-status/)
- [Fonnte Webhook Documentation](https://docs.fonnte.com/webhook-reply-message/)
- [Fonnte Device Status API](https://docs.fonnte.com/device/)

---

## Quick Diagnosis Checklist

- [ ] Laravel logs show `"process": "pending"`
- [ ] Device status shows **disconnected**
- [ ] Fonnte dashboard shows device offline
- [ ] Messages stuck in queue for >1 minute

**If all checked**: Device is disconnected. Reconnect in Fonnte Dashboard.

- [ ] Device status shows **connected**
- [ ] Messages still not delivered after 5 minutes
- [ ] No errors in Laravel logs

**If all checked**: Contact Fonnte support or check for rate limiting.
