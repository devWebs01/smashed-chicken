<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceSynced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Device $device
    ) {}
}
