<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Send notification when order status changes
     */
    public function notifyStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        // Skip if status didn't actually change
        if ($oldStatus === $newStatus) {
            return;
        }

        // Skip notification for offline orders (no device_id)
        if (! $order->device_id) {
            Log::info('Skipping notification for offline order', [
                'order_id' => $order->id,
                'customer_phone' => $order->customer_phone,
                'status_change' => $oldStatus.' -> '.$newStatus,
                'reason' => 'No device_id - offline order',
            ]);

            return;
        }

        // Load order relationships
        $order->load('orderItems.product', 'device');

        // Get device for sending message
        $device = $order->device;
        if (! $device) {
            Log::warning('Device not found for order status notification', [
                'order_id' => $order->id,
                'device_id' => $order->device_id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);

            return;
        }

        // Get message template for status change
        $message = $this->getStatusChangeMessage($order, $oldStatus, $newStatus);
        if (! $message) {
            Log::info('No notification message for status change', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);

            return;
        }

        try {
            $this->fonnteService->sendWhatsAppMessage($order->customer_phone, $message, $device->token);
            Log::info('Order status change notification sent', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status notification', [
                'order_id' => $order->id,
                'status_change' => $oldStatus.' -> '.$newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get message template for status change
     */
    private function getStatusChangeMessage(Order $order, string $oldStatus, string $newStatus): ?string
    {
        $templateKey = 'status_'.$newStatus;

        // Special handling for cancelled status
        if ($newStatus === Order::STATUS_CANCELLED) {
            $templateKey = 'status_cancelled';
        }

        $template = config('whatsapp.messages.'.$templateKey);

        if (! $template) {
            return null;
        }

        // Build order items text
        $itemsText = '';
        foreach ($order->orderItems as $item) {
            $itemsText .= "• {$item->product->name} x{$item->quantity}\n";
        }

        // Replace placeholders
        $message = str_replace(
            [
                '{order_id}',
                '{items}',
                '{delivery}',
                '{address}',
                '{payment}',
                '{total}',
            ],
            [
                $order->id,
                $itemsText,
                $order->delivery_method,
                $order->customer_address ?: 'N/A',
                $order->payment_method,
                number_format($order->total_price, 0, ',', '.'),
            ],
            $template
        );

        return $message;
    }
}
