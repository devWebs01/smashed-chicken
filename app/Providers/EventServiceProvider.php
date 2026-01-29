<?php

namespace App\Providers;

use App\Events\DeviceNotFound;
use App\Events\DeviceSynced;
use App\Listeners\LogDeviceNotFound;
use App\Listeners\LogDeviceSynced;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DeviceNotFound::class => [
            LogDeviceNotFound::class,
        ],
        DeviceSynced::class => [
            LogDeviceSynced::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
