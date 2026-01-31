<?php

namespace App\Listeners;

use App\Events\DeviceSynced;
use Illuminate\Support\Facades\Log;

class LogDeviceSynced
{
    public function handle(DeviceSynced $deviceSynced): void
    {
        Log::info('Device auto-synced successfully', [
            'device_id' => $deviceSynced->device->id,
            'device_phone' => $deviceSynced->device->device,
            'device_name' => $deviceSynced->device->name,
        ]);
    }
}
