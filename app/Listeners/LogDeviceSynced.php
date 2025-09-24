<?php

namespace App\Listeners;

use App\Events\DeviceSynced;
use Illuminate\Support\Facades\Log;

class LogDeviceSynced
{
    public function handle(DeviceSynced $event): void
    {
        Log::info('Device auto-synced successfully', [
            'device_id' => $event->device->id,
            'device_phone' => $event->device->device,
            'device_name' => $event->device->name,
        ]);
    }
}
