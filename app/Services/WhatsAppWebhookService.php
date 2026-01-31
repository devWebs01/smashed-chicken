<?php

namespace App\Services;

use App\DTOs\WebhookData;
use App\Events\DeviceNotFound;
use App\Events\DeviceSynced;
use App\Models\Device;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookService
{
    public function __construct(
        private readonly FonnteService $fonnteService,
        private readonly OrderParser $orderParser,
        private readonly WhatsAppOrderService $whatsAppOrderService,
        private readonly SlackNotificationService $slackNotificationService
    ) {}

    public function handle(array $data): array
    {
        $webhookData = WebhookData::fromArray($data);

        // Only process text messages
        if (($data['type'] ?? 'text') !== 'text') {
            Log::info('Ignoring non-text message', ['type' => $data['type'] ?? 'unknown', 'data' => $data]);

            return ['status' => 'ignored'];
        }

        // Idempotency check
        $dedupKey = $webhookData->getDedupKey();
        if (Cache::has($dedupKey)) {
            Log::info('Ignoring duplicate message', ['dedup_key' => $dedupKey]);

            return ['status' => 'duplicate'];
        }

        Cache::put($dedupKey, true, config('whatsapp.cache_ttl.dedup'));

        Log::info('Processing text message', [
            'sender' => $data['sender'] ?? null,
            'message' => $data['message'] ?? null,
            'device' => $data['device'] ?? null,
        ]);

        if (! $webhookData->sender || ! $webhookData->devicePhone) {
            Log::error('Missing sender or device phone in webhook data', ['data' => $data]);

            // Send Slack notification for missing data
            if ($this->slackNotificationService->isConfigured()) {
                $this->slackNotificationService->webhookError([
                    'error' => 'Missing sender or device phone',
                    'data' => $data,
                ]);
            }

            return ['status' => 'error', 'code' => 400];
        }

        // Find or sync device
        $device = $this->findOrSyncDevice($webhookData->devicePhone);
        if (! $device instanceof \App\Models\Device) {
            Log::error('Device not found or failed to sync', ['device_phone' => $webhookData->devicePhone]);

            // Send Slack notification for device not found
            if ($this->slackNotificationService->isConfigured()) {
                $this->slackNotificationService->webhookError([
                    'error' => 'Device not found or failed to sync',
                    'device_phone' => $webhookData->devicePhone,
                    'sender' => $webhookData->sender,
                ]);
            }

            return ['status' => 'error', 'code' => 400];
        }

        $deviceToken = $device->token;

        try {
            $this->processMessage($webhookData, $deviceToken);

            // Log incoming message (Phase 3: Optional Enhancements)
            $result = ['status' => 'ok'];
            $this->logIncomingMessage($webhookData, $result);

            return $result;
        } catch (\Exception $exception) {
            Log::error('Error processing webhook: '.$exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $errorMessage = config('whatsapp.messages.system_error');
            $this->fonnteService->sendWhatsAppMessage($webhookData->sender, $errorMessage, $deviceToken);

            // Send Slack notification for errors (Phase 3: Optional Enhancements)
            if ($this->slackNotificationService->isConfigured()) {
                $this->slackNotificationService->webhookError([
                    'error' => $exception->getMessage(),
                    'sender' => $webhookData->sender,
                    'device' => $webhookData->devicePhone,
                    'message' => $webhookData->message,
                    'trace' => $exception->getTraceAsString(),
                ]);
            }

            return ['status' => 'error'];
        }
    }

    private function processMessage(WebhookData $webhookData, string $deviceToken): array
    {
        $phoneKey = $webhookData->getCanonicalSender();

        // Store the inboxId for potential reply (quote reply feature)
        if ($webhookData->inboxId) {
            Cache::put('last_inbox_'.$phoneKey, $webhookData->inboxId, 300);
        }

        $customerStep = Cache::get('customer_'.$phoneKey);

        // Handle customer info collection
        if (! $customerStep) {
            $this->handleNewCustomer($phoneKey, $webhookData->sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($customerStep === 'await_name') {
            $this->handleCustomerName($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($customerStep === 'await_address') {
            $this->handleCustomerAddress($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Handle commands and orders
        return $this->handleCustomerCommands($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken, $webhookData);
    }

    private function handleNewCustomer(string $phoneKey, string $sender, string $deviceToken): array
    {
        $message = config('whatsapp.messages.welcome');
        Cache::put('customer_'.$phoneKey, 'await_name', config('whatsapp.cache_ttl.customer_info'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleCustomerName(string $phoneKey, string $message, string $sender, string $deviceToken): array
    {
        $customerName = trim($message);
        if ($customerName === '' || $customerName === '0') {
            $message = config('whatsapp.messages.name_empty');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        Cache::put('customer_name_'.$phoneKey, $customerName, config('whatsapp.cache_ttl.customer_info'));
        Cache::put('customer_'.$phoneKey, 'await_address', config('whatsapp.cache_ttl.customer_info'));
        $message = str_replace('{name}', $customerName, config('whatsapp.messages.address_prompt'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleCustomerAddress(string $phoneKey, string $message, string $sender, string $deviceToken): array
    {
        $customerAddress = trim($message);
        Cache::put('customer_address_'.$phoneKey, $customerAddress, config('whatsapp.cache_ttl.customer_info'));
        Cache::put('customer_'.$phoneKey, 'info_complete', config('whatsapp.cache_ttl.customer_info'));
        $message = config('whatsapp.messages.info_complete');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleCustomerCommands(string $phoneKey, string $message, string $sender, string $deviceToken, WebhookData $webhookData): array
    {
        // Handle special commands
        if ($this->isCommand($message, 'tambah')) {
            $this->handleTambah($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($this->isCommand($message, 'selesai')) {
            $addToOrderCache = Cache::get('add_to_order_'.$phoneKey);
            Cache::forget('add_to_order_'.$phoneKey);

            // If finishing add to cached order, show order review
            if ($addToOrderCache === 'cache_order') {
                $orderData = Cache::get('order_'.$phoneKey);
                if ($orderData) {
                    $this->handleConfirmOrder($webhookData, $sender, $deviceToken);

                    return ['status' => 'ok'];
                }
            }

            $message = config('whatsapp.messages.order_complete');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($this->isCommand($message, 'reset')) {
            $this->handleReset($phoneKey, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if (str_starts_with($message, 'batal ')) {
            $this->handleBatalCommand($message, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($this->isCommand($message, 'batal')) {
            $this->showPendingOrders($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Handle confirmations
        if ($this->isCommand($message, 'confirm')) {
            $this->handleConfirmOrder($webhookData, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Handle edits
        if ($this->isCommand($message, 'edit')) {
            $this->whatsAppOrderService->cancelOrder($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Check for existing order flow
        $orderCacheKey = 'order_'.$phoneKey;
        if (Cache::has($orderCacheKey)) {
            // Handle menu request when order is pending - show menu and enable add to order mode
            if ($this->isMenuRequest($message)) {
                $this->handleMenuWithPendingOrder($phoneKey, $sender, $deviceToken);

                return ['status' => 'ok'];
            }

            // Handle order confirmation flow for all other messages
            $this->handleConfirmOrder($webhookData, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Check for menu keywords (when no pending order)
        if ($this->isMenuRequest($message)) {
            $this->sendProductMenu($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Try to parse product selections
        $selections = $this->orderParser->parse($message);
        if ($selections !== []) {
            $this->whatsAppOrderService->processSelections($selections, $webhookData, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Default reply
        $this->sendDefaultReply($sender, $deviceToken);

        return ['status' => 'ok'];
    }

    private function isCommand(string $message, string $command): bool
    {
        return in_array($message, config('whatsapp.keywords.'.$command, []));
    }

    private function isMenuRequest(string $message): bool
    {
        $menuKeywords = config('whatsapp.keywords.menu', []);
        foreach ($menuKeywords as $menuKeyword) {
            if (str_contains(strtolower($message), (string) $menuKeyword)) {
                return true;
            }
        }

        return false;
    }

    private function handleReset(string $phoneKey, string $sender, string $deviceToken): array
    {
        Cache::forget('order_'.$phoneKey);
        Cache::forget('add_to_order_'.$phoneKey);
        Cache::forget('customer_'.$phoneKey);
        Cache::forget('customer_name_'.$phoneKey);
        Cache::forget('customer_address_'.$phoneKey);
        $message = config('whatsapp.messages.reset_done');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleBatalCommand(string $message, string $sender, string $deviceToken): array
    {
        $parts = explode(' ', $message);
        if (count($parts) === 2 && is_numeric($parts[1])) {
            $this->handleBatal($sender, $deviceToken, (int) $parts[1]);
        } else {
            $this->sendDefaultReply($sender, $deviceToken);
        }

        return ['status' => 'ok'];
    }

    private function findOrSyncDevice(string $devicePhone): ?Device
    {
        $normalizedDevice = $this->normalizePhone($devicePhone);
        $altDevice = $this->altPhoneFormat($devicePhone);

        $device = Device::where('device', $normalizedDevice)->orWhere('device', $altDevice)->first();

        if (! $device) {
            // Try to sync from Fonnte
            $res = $this->fonnteService->getAllDevices();
            if ($res['status'] && isset($res['data']['data'])) {
                foreach ($res['data']['data'] as $fonnteDevice) {
                    $fonntePhone = $fonnteDevice['device'];
                    $fonnteNormalized = $this->normalizePhone($fonntePhone);
                    if ($fonnteNormalized === $normalizedDevice || $fonnteNormalized === $altDevice) {
                        $device = Device::create([
                            'name' => 'Auto-synced Device',
                            'device' => $fonnteNormalized,
                            'token' => $fonnteDevice['token'],
                        ]);
                        event(new DeviceSynced($device));
                        break;
                    }
                }
            }

            if (! $device) {
                event(new DeviceNotFound($devicePhone));
            }
        }

        return $device;
    }

    private function canonicalPhone(string $raw): string
    {
        $p = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with((string) $p, '0')) {
            return '62'.substr((string) $p, 1);
        }

        return $p;
    }

    private function normalizePhone(string $phone): string
    {
        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;
    }

    private function altPhoneFormat(string $phone): string
    {
        return str_starts_with($phone, '62') ? '0'.substr($phone, 2) : $phone;
    }

    // Delegate methods to services
    private function handleTambah(string $sender, string $deviceToken): array
    {
        $phoneKey = $this->canonicalPhone($sender);
        $lastOrder = \App\Models\Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->latest()
            ->first();

        if (! $lastOrder) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        $itemsText = '';
        foreach ($lastOrder->orderItems as $item) {
            $itemsText .= sprintf('• %s x%s%s', $item->product->name, $item->quantity, PHP_EOL);
        }

        $message = str_replace(
            ['{id}', '{total}', '{items}'],
            [$lastOrder->id, number_format($lastOrder->total_price, 0, ',', '.'), $itemsText],
            config('whatsapp.messages.last_order')
        );

        Cache::put('add_to_order_'.$phoneKey, $lastOrder->id, config('whatsapp.cache_ttl.add_to_order'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function showPendingOrders(string $sender, string $deviceToken): array
    {
        $phoneKey = $this->canonicalPhone($sender);
        $pendingOrders = \App\Models\Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $message = config('whatsapp.messages.no_pending_orders');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        $ordersText = '';
        foreach ($pendingOrders as $index => $order) {
            $ordersText .= ($index + 1).'. Order #'.$order->id.' - Total: Rp '.number_format($order->total_price, 0, ',', '.')."\n";
            foreach ($order->orderItems as $item) {
                $ordersText .= sprintf('   • %s x%s%s', $item->product->name, $item->quantity, PHP_EOL);
            }

            $ordersText .= "\n";
        }

        $message = str_replace('{orders}', $ordersText, config('whatsapp.messages.pending_orders'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleBatal(string $sender, string $deviceToken, int $orderIndex): array
    {
        $phoneKey = $this->canonicalPhone($sender);
        $pendingOrders = \App\Models\Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orderIndex < 1 || $orderIndex > $pendingOrders->count()) {
            $message = config('whatsapp.messages.invalid_order_index');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        $order = $pendingOrders->get($orderIndex - 1);
        $order->update(['status' => 'cancelled']);

        $message = str_replace('{id}', $order->id, config('whatsapp.messages.order_cancelled_success'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function sendProductMenu(string $sender, string $deviceToken): array
    {
        $products = \App\Models\Product::all();

        $message = "*Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1).sprintf('. *%s* - Rp ', $product->name).number_format($product->price, 0, ',', '.')."\n";
        }

        $message .= "----------------------------\n";
        $message .= "📝 *CARA PEMESANAN:*\n\n";
        $message .= "• *1 produk:* Ketik nomor produk\n";
        $message .= "   Contoh: *1* (1 porsi)\n\n";
        $message .= "• *Jumlah lebih:* nomor=jumlah\n";
        $message .= "   Contoh: *1=3* (3 porsi produk 1)\n\n";
        $message .= "• *Multiple produk:* pisahkan koma\n";
        $message .= "   Contoh: *1=2, 2=1* (2 porsi produk 1 + 1 porsi produk 2)\n\n";
        $message .= '📋 Ketik *menu* kapan saja untuk lihat menu lagi';

        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function sendDefaultReply(string $sender, string $deviceToken): array
    {
        $message = config('whatsapp.messages.default_reply');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    private function handleConfirmOrder(WebhookData $webhookData, string $sender, string $deviceToken): array
    {
        $phoneKey = $this->canonicalPhone($sender);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        // Check timeout
        $createdAt = $orderData['created_at'] ?? now()->timestamp;
        if (now()->timestamp - $createdAt > config('whatsapp.cache_ttl.order')) {
            Cache::forget('order_'.$phoneKey);
            $message = config('whatsapp.messages.order_expired');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return ['status' => 'ok'];
        }

        $step = $orderData['step'] ?? 'await_delivery_method';

        // Step: await_confirmation is REMOVED - no longer used!
        // Order creation now directly sets 'await_delivery_method' and sends prompt immediately

        if ($step === 'await_delivery_method') {
            $deliveryMethod = $webhookData->getNormalizedMessage();
            Log::info('Delivery method received', [
                'raw_message' => $webhookData->message,
                'normalized' => $deliveryMethod,
                'valid_options' => ['takeaway', 'delivery'],
            ]);

            if (! in_array($deliveryMethod, ['takeaway', 'delivery'])) {
                $message = config('whatsapp.messages.invalid_delivery');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return ['status' => 'ok'];
            }

            $orderData['delivery_method'] = $deliveryMethod;
            if ($deliveryMethod === 'delivery') {
                $orderData['step'] = 'await_address';
                Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
                $message = config('whatsapp.messages.address_prompt_delivery');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return ['status' => 'ok'];
            }

            $orderData['step'] = 'await_payment_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->askPaymentMethod($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($step === 'await_address') {
            $orderData['customer_address'] = trim($webhookData->message ?? '');
            $orderData['step'] = 'await_payment_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->askPaymentMethod($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($step === 'await_payment_method') {
            $paymentMethod = $webhookData->getNormalizedMessage();
            if (! in_array($paymentMethod, ['cash', 'transfer'])) {
                $message = config('whatsapp.messages.invalid_payment');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return ['status' => 'ok'];
            }

            $orderData['payment_method'] = $paymentMethod;
            $orderData['step'] = 'ready_to_confirm';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->whatsAppOrderService->showFinalReview($orderData, $sender, $deviceToken);

            return ['status' => 'ok'];
        }

        if ($step === 'ready_to_confirm') {
            $result = $this->whatsAppOrderService->confirmOrder($orderData, $sender, $deviceToken);
            if ($result['status'] === 'error') {
                $this->fonnteService->sendWhatsAppMessage($sender, $result['message'], $deviceToken);
            }
        }

        return ['status' => 'ok'];
    }

    private function askPaymentMethod(string $sender, string $deviceToken): void
    {
        $message = config('whatsapp.messages.payment_prompt');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleMenuWithPendingOrder(string $phoneKey, string $sender, string $deviceToken): array
    {
        // Get current order data
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $this->sendProductMenu($sender, $deviceToken);

            return ['status' => 'ok'];
        }

        // Show current order items
        $currentItems = '';
        foreach ($orderData['selections'] as $sel) {
            $currentItems .= sprintf('• %s x%s - Rp ', $sel['product_name'], $sel['quantity']).number_format($sel['subtotal'], 0, ',', '.')."\n";
        }

        $message = "*Pesanan Anda Saat Ini:*\n{$currentItems}\n";
        $message .= '*Total: Rp '.number_format($orderData['total'], 0, ',', '.')."*\n\n";

        // Show product menu
        $products = \App\Models\Product::all();
        $message .= "*Menu Tambahan:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1).sprintf('. *%s* - Rp ', $product->name).number_format($product->price, 0, ',', '.')."\n";
        }

        $message .= "\n----------------------------\n";
        $message .= "📝 *CARA MENAMBAH PESANAN:*\n\n";
        $message .= "• *1 produk:* Ketik nomor produk\n";
        $message .= "   Contoh: *1* (tambah 1 porsi)\n\n";
        $message .= "• *Jumlah lebih:* nomor=jumlah\n";
        $message .= "   Contoh: *1=3* (tambah 3 porsi produk 1)\n\n";
        $message .= "• *Multiple:* *1=2, 2=1*\n\n";
        $message .= "✅ Ketik *selesai* untuk lanjut pembayaran\n";
        $message .= '📋 Ketik *menu* untuk lihat menu lagi';

        // Enable add to order mode
        Cache::put('add_to_order_'.$phoneKey, 'cache_order', config('whatsapp.cache_ttl.add_to_order'));

        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

        return ['status' => 'ok'];
    }

    /**
     * Log incoming message to database (Phase 3: Optional Enhancements)
     *
     * @param  WebhookData  $webhookData  Webhook data
     * @param  array  $result  Processing result
     */
    private function logIncomingMessage(WebhookData $webhookData, array $result): void
    {
        try {
            MessageLog::create([
                'sender' => $webhookData->sender,
                'device' => $webhookData->devicePhone,
                'message' => $webhookData->message,
                'inbox_id' => $webhookData->inboxId,
                'whatsapp_timestamp' => $webhookData->timestamp
                    ? date('Y-m-d H:i:s', $webhookData->timestamp)
                    : null,
                'type' => $webhookData->type ?? 'text',
                'direction' => 'incoming',
                'status_code' => 200,
                'response' => $result,
                'sender_name' => $webhookData->name,
            ]);
        } catch (\Exception $exception) {
            // Don't fail the webhook if logging fails
            Log::warning('Failed to log message', [
                'error' => $exception->getMessage(),
                'sender' => $webhookData->sender,
            ]);
        }
    }
}
