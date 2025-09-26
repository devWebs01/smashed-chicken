<?php

namespace App\Services;

use App\DTOs\WebhookData;
use App\Events\DeviceNotFound;
use App\Events\DeviceSynced;
use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookService
{
    public function __construct(
        private FonnteService $fonnteService,
        private OrderParser $orderParser,
        private WhatsAppOrderService $orderService
    ) {}

    public function handle(array $data): array
    {
        // Handle GET requests (for verification)
        if (request()->isMethod('get')) {
            return ['status' => 'ok'];
        }

        $webhookData = WebhookData::fromArray($data);

        // Only process text messages
        if (($data['type'] ?? 'text') !== 'text') {
            Log::info('Ignoring non-text message', ['type' => $data['type'] ?? 'unknown']);

            return ['status' => 'ignored'];
        }

        // Idempotency check
        $dedupKey = $webhookData->getDedupKey();
        if (Cache::has($dedupKey)) {
            return ['status' => 'duplicate'];
        }
        Cache::put($dedupKey, true, config('whatsapp.cache_ttl.dedup'));

        Log::info('Processing text message', [
            'sender' => $data['sender'] ?? null,
            'message' => $data['message'] ?? null,
            'device' => $data['device'] ?? null,
        ]);

        if (! $webhookData->sender || ! $webhookData->devicePhone) {
            return ['status' => 'error', 'code' => 400];
        }

        // Find or sync device
        $device = $this->findOrSyncDevice($webhookData->devicePhone);
        if (! $device) {
            return ['status' => 'error', 'code' => 400];
        }
        $deviceToken = $device->token;

        try {
            $this->processMessage($webhookData, $deviceToken);

            return ['status' => 'ok'];
        } catch (\Exception $e) {
            Log::error('Error processing webhook: '.$e->getMessage());
            $errorMessage = config('whatsapp.messages.system_error');
            $this->fonnteService->sendWhatsAppMessage($webhookData->sender, $errorMessage, $deviceToken);

            return ['status' => 'error'];
        }
    }

    private function processMessage(WebhookData $webhookData, string $deviceToken): void
    {
        $phoneKey = $webhookData->getCanonicalSender();
        $customerStep = Cache::get('customer_'.$phoneKey);

        // Handle customer info collection
        if (! $customerStep) {
            $this->handleNewCustomer($phoneKey, $webhookData->sender, $deviceToken);

            return;
        }

        if ($customerStep === 'await_name') {
            $this->handleCustomerName($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken);

            return;
        }

        if ($customerStep === 'await_address') {
            $this->handleCustomerAddress($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken);

            return;
        }

        // Handle commands and orders
        $this->handleCustomerCommands($phoneKey, $webhookData->getNormalizedMessage(), $webhookData->sender, $deviceToken, $webhookData);
    }

    private function handleNewCustomer(string $phoneKey, string $sender, string $deviceToken): void
    {
        $message = config('whatsapp.messages.welcome');
        Cache::put('customer_'.$phoneKey, 'await_name', config('whatsapp.cache_ttl.customer_info'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleCustomerName(string $phoneKey, string $message, string $sender, string $deviceToken): void
    {
        $customerName = trim($message);
        if (empty($customerName)) {
            $message = config('whatsapp.messages.name_empty');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }
        Cache::put('customer_name_'.$phoneKey, $customerName, config('whatsapp.cache_ttl.customer_info'));
        Cache::put('customer_'.$phoneKey, 'await_address', config('whatsapp.cache_ttl.customer_info'));
        $message = str_replace('{name}', $customerName, config('whatsapp.messages.address_prompt'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleCustomerAddress(string $phoneKey, string $message, string $sender, string $deviceToken): void
    {
        $customerAddress = trim($message);
        Cache::put('customer_address_'.$phoneKey, $customerAddress, config('whatsapp.cache_ttl.customer_info'));
        Cache::put('customer_'.$phoneKey, 'info_complete', config('whatsapp.cache_ttl.customer_info'));
        $message = config('whatsapp.messages.info_complete');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleCustomerCommands(string $phoneKey, string $message, string $sender, string $deviceToken, WebhookData $webhookData): void
    {
        // Handle special commands
        if ($this->isCommand($message, 'tambah')) {
            $this->handleTambah($webhookData, $sender, $deviceToken);

            return;
        }

        if ($this->isCommand($message, 'selesai')) {
            Cache::forget('add_to_order_'.$phoneKey);
            $message = config('whatsapp.messages.order_complete');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        if ($this->isCommand($message, 'reset')) {
            $this->handleReset($phoneKey, $sender, $deviceToken);

            return;
        }

        if (str_starts_with($message, 'batal ')) {
            $this->handleBatalCommand($message, $sender, $deviceToken);

            return;
        }

        if ($this->isCommand($message, 'batal')) {
            $this->showPendingOrders($sender, $deviceToken);

            return;
        }

        // Check for menu keywords
        if ($this->isMenuRequest($message)) {
            $this->sendProductMenu($sender, $deviceToken);

            return;
        }

        // Handle confirmations
        if ($this->isCommand($message, 'confirm')) {
            $this->handleConfirmOrder($webhookData, $sender, $deviceToken);

            return;
        }

        // Handle edits
        if ($this->isCommand($message, 'edit')) {
            $this->orderService->cancelOrder($sender, $deviceToken);

            return;
        }

        // Check for existing order flow
        $orderCacheKey = 'order_'.$phoneKey;
        if (Cache::has($orderCacheKey)) {
            $this->handleConfirmOrder($webhookData, $sender, $deviceToken);

            return;
        }

        // Try to parse product selections
        $selections = $this->orderParser->parse($message);
        if (! empty($selections)) {
            $this->orderService->processSelections($selections, $webhookData, $sender, $deviceToken);

            return;
        }

        // Default reply
        $this->sendDefaultReply($sender, $deviceToken);
    }

    private function isCommand(string $message, string $command): bool
    {
        return in_array($message, config('whatsapp.keywords.'.$command, []));
    }

    private function isMenuRequest(string $message): bool
    {
        $menuKeywords = config('whatsapp.keywords.menu', []);
        foreach ($menuKeywords as $keyword) {
            if (str_contains(strtolower($message), $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function handleReset(string $phoneKey, string $sender, string $deviceToken): void
    {
        Cache::forget('order_'.$phoneKey);
        Cache::forget('add_to_order_'.$phoneKey);
        Cache::forget('customer_'.$phoneKey);
        Cache::forget('customer_name_'.$phoneKey);
        Cache::forget('customer_address_'.$phoneKey);
        $message = config('whatsapp.messages.reset_done');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleBatalCommand(string $message, string $sender, string $deviceToken): void
    {
        $parts = explode(' ', $message);
        if (count($parts) == 2 && is_numeric($parts[1])) {
            $this->handleBatal($sender, $deviceToken, (int) $parts[1]);
        } else {
            $this->sendDefaultReply($sender, $deviceToken);
        }
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

    private function normalizeMessage(string $message): string
    {
        return strtolower(trim($message, " \t\n\r\0\x0B'\""));
    }

    private function canonicalPhone(string $raw): string
    {
        $p = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($p, '0')) {
            $p = '62'.substr($p, 1);
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
    private function handleTambah(WebhookData $webhookData, string $sender, string $deviceToken): void
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

            return;
        }

        $itemsText = '';
        foreach ($lastOrder->orderItems as $item) {
            $itemsText .= "• {$item->product->name} x{$item->quantity}\n";
        }

        $message = str_replace(
            ['{id}', '{total}', '{items}'],
            [$lastOrder->id, number_format($lastOrder->total_price, 0, ',', '.'), $itemsText],
            config('whatsapp.messages.last_order')
        );

        Cache::put('add_to_order_'.$phoneKey, $lastOrder->id, config('whatsapp.cache_ttl.add_to_order'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function showPendingOrders(string $sender, string $deviceToken): void
    {
        $phoneKey = $this->canonicalPhone($sender);
        $pendingOrders = \App\Models\Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->with('orderItems.product')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $message = config('whatsapp.messages.no_pending_orders');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        $ordersText = '';
        foreach ($pendingOrders as $index => $order) {
            $ordersText .= ($index + 1).'. Order #'.$order->id.' - Total: Rp '.number_format($order->total_price, 0, ',', '.')."\n";
            foreach ($order->orderItems as $item) {
                $ordersText .= "   • {$item->product->name} x{$item->quantity}\n";
            }
            $ordersText .= "\n";
        }

        $message = str_replace('{orders}', $ordersText, config('whatsapp.messages.pending_orders'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleBatal(string $sender, string $deviceToken, int $orderIndex): void
    {
        $phoneKey = $this->canonicalPhone($sender);
        $pendingOrders = \App\Models\Order::where('customer_phone', $phoneKey)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orderIndex < 1 || $orderIndex > $pendingOrders->count()) {
            $message = config('whatsapp.messages.invalid_order_index');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        $order = $pendingOrders->get($orderIndex - 1);
        $order->update(['status' => 'cancelled']);

        $message = str_replace('{id}', $order->id, config('whatsapp.messages.order_cancelled_success'));
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function sendProductMenu(string $sender, string $deviceToken): void
    {
        $products = \App\Models\Product::all();

        $message = "*Menu Makanan Kami:*\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1).". *{$product->name}* - Rp ".number_format($product->price, 0, ',', '.')."\n";

        }

        $message .= "----------------------------\n";
        $message .= "*Cara Pemesanan:*\n";
        $message .= "• Ketik nomor produk untuk pesan 1 porsi.\n";
        $message .= "   Contoh: *1*\n";
        $message .= "• Ketik nomor + jumlah untuk pesan lebih dari 1.\n";
        $message .= "   Contoh: *1 3* atau *1=3* (3 porsi produk 1)\n";
        $message .= "• Untuk pesan multiple produk, pisahkan dengan koma.\n";
        $message .= "   Contoh: *1=2, 2=1* (2 porsi produk 1 + 1 porsi produk 2)\n\n";
        $message .= 'Ketik menu kapan saja untuk melihat daftar produk lagi.';

        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function sendDefaultReply(string $sender, string $deviceToken): void
    {
        $message = config('whatsapp.messages.default_reply');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }

    private function handleConfirmOrder(WebhookData $webhookData, string $sender, string $deviceToken): void
    {
        $phoneKey = $this->canonicalPhone($sender);
        $orderData = Cache::get('order_'.$phoneKey);

        if (! $orderData) {
            $message = config('whatsapp.messages.no_pending_order');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        // Check timeout
        $createdAt = $orderData['created_at'] ?? now()->timestamp;
        if (now()->timestamp - $createdAt > config('whatsapp.cache_ttl.order')) {
            Cache::forget('order_'.$phoneKey);
            $message = config('whatsapp.messages.order_expired');
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        $step = $orderData['step'] ?? 'await_confirmation';

        if ($step === 'await_confirmation') {
            $message = config('whatsapp.messages.delivery_prompt');
            $orderData['step'] = 'await_delivery_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

            return;
        }

        if ($step === 'await_delivery_method') {
            $deliveryMethod = strtolower(trim($webhookData->message ?? ''));
            if (! in_array($deliveryMethod, ['takeaway', 'delivery'])) {
                $message = config('whatsapp.messages.invalid_delivery');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }
            $orderData['delivery_method'] = $deliveryMethod;
            if ($deliveryMethod === 'delivery') {
                $orderData['step'] = 'await_address';
                Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
                $message = config('whatsapp.messages.address_prompt_delivery');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            } else {
                $orderData['step'] = 'await_payment_method';
                Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
                $this->askPaymentMethod($sender, $deviceToken);

                return;
            }
        }

        if ($step === 'await_address') {
            $orderData['customer_address'] = trim($webhookData->message ?? '');
            $orderData['step'] = 'await_payment_method';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->askPaymentMethod($sender, $deviceToken);

            return;
        }

        if ($step === 'await_payment_method') {
            $paymentMethod = strtolower(trim($webhookData->message ?? ''));
            if (! in_array($paymentMethod, ['cash', 'transfer'])) {
                $message = config('whatsapp.messages.invalid_payment');
                $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);

                return;
            }
            $orderData['payment_method'] = $paymentMethod;
            $orderData['step'] = 'ready_to_confirm';
            Cache::put('order_'.$phoneKey, $orderData, config('whatsapp.cache_ttl.order'));
            $this->orderService->showFinalReview($orderData, $sender, $deviceToken);

            return;
        }

        if ($step === 'ready_to_confirm') {
            $result = $this->orderService->confirmOrder($orderData, $sender, $deviceToken);
            if ($result['status'] === 'error') {
                $this->fonnteService->sendWhatsAppMessage($sender, $result['message'], $deviceToken);
            }
        }
    }

    private function askPaymentMethod(string $sender, string $deviceToken): void
    {
        $message = config('whatsapp.messages.payment_prompt');
        $this->fonnteService->sendWhatsAppMessage($sender, $message, $deviceToken);
    }
}
