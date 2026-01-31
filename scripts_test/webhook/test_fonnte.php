<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Device;
use App\Services\FonnteService;

$app = require __DIR__.'/bootstrap/app.php';

echo "========================================\n";
echo "Testing Fonnte Service\n";
echo "========================================\n\n";

// 1. Check devices in database
echo "1. Devices in Database:\n";
echo "-------------------------\n";
$devices = Device::all();
foreach ($devices as $device) {
    echo "ID: {$device->id}\n";
    echo "Name: {$device->name}\n";
    echo "Device (Phone): {$device->device}\n";
    echo 'Token: '.substr($device->token, 0, 20)."...\n";
    echo "-------------------------\n";
}

// 2. Test Fonnte API directly
echo "\n2. Testing Fonnte API Direct:\n";
echo "------------------------------\n";

$fonnteService = new FonnteService;

// Get account token from env
$accountToken = env('ACCOUNT_TOKEN');
echo 'Account Token: '.substr($accountToken, 0, 20)."...\n\n";

// Test get all devices
echo "Fetching all devices from Fonnte...\n";
$result = $fonnteService->getAllDevices();
echo 'Result: '.json_encode($result, JSON_PRETTY_PRINT)."\n\n";

if ($result['status'] && isset($result['data']['data'])) {
    echo "Fonnte Devices:\n";
    foreach ($result['data']['data'] as $fonnteDevice) {
        echo "- Phone: {$fonnteDevice['device']}, Name: {$fonnteDevice['name']}\n";
    }
}

// 3. Test sending message directly
echo "\n3. Test Sending Message:\n";
echo "------------------------------\n";

$testDevice = null;
if (! $devices->isEmpty()) {
    $testDevice = $devices->first();
    echo "Using device from DB: {$testDevice->device}\n";
    echo 'Device Token: '.substr($testDevice->token, 0, 20)."...\n\n";
} else {
    echo "No device in database. Using Fonnte device from API...\n";
    if ($result['status'] && isset($result['data']['data']) && count($result['data']['data']) > 0) {
        $fonnteDevice = $result['data']['data'][0];
        $testDevice = (object) [
            'device' => $fonnteDevice['device'],
            'token' => $fonnteDevice['token'],
        ];
        echo "Using Fonnte device: {$testDevice->device}\n\n";
    } else {
        echo "ERROR: No device available!\n";
        exit(1);
    }
}

// Send test message
$testPhone = '628978301766'; // Ganti dengan nomor tujuan
$testMessage = 'Test message dari sistem - '.date('Y-m-d H:i:s');

echo "Sending to: {$testPhone}\n";
echo "Message: {$testMessage}\n\n";

$sendResult = $fonnteService->sendWhatsAppMessage(
    $testPhone,
    $testMessage,
    $testDevice->token
);

echo "Result:\n";
echo json_encode($sendResult, JSON_PRETTY_PRINT)."\n";

echo "\n========================================\n";
echo "Test completed!\n";
echo "========================================\n";
