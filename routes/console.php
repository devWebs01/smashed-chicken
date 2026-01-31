<?php

use App\Console\Commands\CheckDeviceStatusCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fonnte device status check
Artisan::command('fonnte:device-status', function () {
    $this->call(CheckDeviceStatusCommand::class);
})->purpose('Check Fonnte device connection status');
