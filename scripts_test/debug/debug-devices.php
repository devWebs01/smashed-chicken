<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$devices = App\Models\Device::all();
echo json_encode($devices->toArray(), JSON_PRETTY_PRINT);
