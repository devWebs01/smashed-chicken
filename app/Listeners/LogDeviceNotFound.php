<?php

namespace App\Listeners;

use App\Events\DeviceNotFound;
use Illuminate\Support\Facades\Log;

class LogDeviceNotFound
{
    public function handle(DeviceNotFound $event): void
    {
        Log::warning('Device not found for webhook', [
            'device_phone' => $event->devicePhone,
        ]);
    }
}
