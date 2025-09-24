<?php

namespace App\Observers;

use App\Models\Device;
use App\Models\Order;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Send confirmation message to customer
        $this->sendOrderConfirmation($order);
    }

    private function sendOrderConfirmation(Order $order)
    {
        // Load order items with products and device
        $order->load('orderItems.product', 'device');

        // Use device from order
        $device = $order->device;
        if (! $device) {
            Log::warning('Device not found for order confirmation', ['order_id' => $order->id, 'device_id' => $order->device_id]);

            return;
        }

        $confirmationMessage = "*Pesanan Dikonfirmasi!*\n\n";
        $confirmationMessage .= 'Order #'.$order->id."\n\n";
        foreach ($order->orderItems as $item) {
            $confirmationMessage .= "*{$item->product->name}* - {$item->quantity} porsi\n";
        }
        $confirmationMessage .= "\nPengiriman: {$order->delivery_method}";
        if ($order->customer_address) {
            $confirmationMessage .= "\nAlamat: {$order->customer_address}";
        }
        $confirmationMessage .= "\nPembayaran: {$order->payment_method}";
        $confirmationMessage .= "\n*Total: Rp ".number_format($order->total_price, 0, ',', '.')."*\n\n";
        $confirmationMessage .= "Terima kasih telah memesan!\n";
        $confirmationMessage .= 'Pesanan Anda sedang diproses.';

        try {
            $this->fonnteService->sendWhatsAppMessage($order->customer_phone, $confirmationMessage, $device->token);
            Log::info('Order confirmation sent', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
