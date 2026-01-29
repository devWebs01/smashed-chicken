<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Starting Fonnte Debug ---\n";

try {
    $service = app(App\Services\FonnteService::class);
    echo "Fetching devices from Fonnte API...\n";
    $devices = $service->getAllDevices();

    echo "Fonnte API Response:\n";
    print_r($devices);

    $target = '628978301766'; // Customer from test script
    $targetDevice = '6285951572182'; // Merchant from test script
    $found = false;

    if (isset($devices['data']) && is_array($devices['data'])) {
        // Fonnte structure might be data->data or just data?
        // Service implementation says: $utils->getAllDevices() returns ['status'=>..., 'data'=>...]
        // Fonnte API returns { "status": true, "data": [ ... ] } usually.
        // Let's inspect what we got.

        $deviceList = $devices['data'];
        if (isset($deviceList['data'])) {
            $deviceList = $deviceList['data'];
        }

        foreach ($deviceList as $d) {
            echo 'Found Device: '.json_encode($d)."\n";
            $currentDevice = $d['device'];

            // Normalize for check
            if (strpos($currentDevice, $targetDevice) !== false || $currentDevice == $targetDevice) {
                $found = true;
                echo ">>> MATCHED TARGET DEVICE: $currentDevice\n";
                $token = $d['token'];
                echo "Sending test message to $target using token $token...\n";
                $res = $service->sendWhatsAppMessage($target, 'Test Outgoing Message from Antigravity Debugger', $token);
                echo "Send Result:\n";
                print_r($res);
            }
        }
    } else {
        echo "No 'data' in device response.\n";
    }

    if (! $found) {
        echo "!!! Target device $targetDevice NOT found in Fonnte account.\n";
        echo "This explains why outgoing messages might fail if the DB has this number but Fonnte doesn't.\n";

        // Try to send using the first available device if any
        if (isset($deviceList) && count($deviceList) > 0) {
            $first = $deviceList[0];
            echo 'Attempting to send using first available device: '.$first['device']."\n";
            $res = $service->sendWhatsAppMessage($target, "Test Output (Fallback Device: {$first['device']})", $first['token']);
            print_r($res);
        }
    }
} catch (\Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
    echo $e->getTraceAsString();
}
echo "--- End Debug ---\n";
