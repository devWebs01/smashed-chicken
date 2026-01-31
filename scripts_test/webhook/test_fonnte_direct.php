<?php

// Test direct Fonnte API tanpa Laravel
// Untuk memverifikasi apakah masalah di middleware atau di Fonnte

$accountToken = '8uk2mrFcmnCd3doPn7DkUgbhAfjqT';
$targetPhone = '628978301766';
$devicePhone = '6285951572182';

// Ambil device token dari database
$databasePath = __DIR__.'/database/database.sqlite';

try {
    $db = new PDO('sqlite:'.$databasePath);

    // Cek device
    $stmt = $db->prepare('SELECT * FROM devices WHERE device = ? LIMIT 1');
    $stmt->execute([$devicePhone]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $device) {
        echo "ERROR: Device tidak ditemukan di database!\n";
        echo "Device Phone: $devicePhone\n";
        exit(1);
    }

    echo "✅ Device ditemukan:\n";
    echo "  ID: {$device['id']}\n";
    echo "  Name: {$device['name']}\n";
    echo "  Device: {$device['device']}\n";
    echo '  Token: '.substr($device['token'], 0, 30)."...\n\n";

    $deviceToken = $device['token'];

} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    exit(1);
}

// Test 1: Kirim pesan menggunakan account token
echo "========================================\n";
echo "Test 1: Kirim dengan Account Token\n";
echo "========================================\n";

$data1 = [
    'target' => $targetPhone,
    'message' => 'Test 1 - Account Token - '.date('H:i:s'),
    'device' => $devicePhone,
];

$ch = curl_init('https://api.fonnte.com/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data1));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: '.$accountToken,
    'Content-Type: application/json',
]);

$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode1\n";
echo "Response: $response1\n\n";

// Test 2: Kirim pesan menggunakan device token
echo "========================================\n";
echo "Test 2: Kirim dengan Device Token\n";
echo "========================================\n";

$data2 = [
    'target' => $targetPhone,
    'message' => 'Test 2 - Device Token - '.date('H:i:s'),
    'device' => $devicePhone,
];

$ch2 = curl_init('https://api.fonnte.com/send');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($data2));
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Authorization: '.$deviceToken,
    'Content-Type: application/json',
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "HTTP Code: $httpCode2\n";
echo "Response: $response2\n\n";

// Decode responses untuk cek status
$resp1 = json_decode($response1, true);
$resp2 = json_decode($response2, true);

echo "========================================\n";
echo "Hasil:\n";
echo "========================================\n";

echo "Test 1 (Account Token):\n";
if ($resp1['status'] ?? false) {
    echo "  ✅ SUCCESS\n";
    echo '  Detail: '.($resp1['detail'] ?? 'N/A')."\n";
    echo '  Process: '.($resp1['process'] ?? 'N/A')."\n";
    echo '  Message ID: '.($resp1['id'][0] ?? 'N/A')."\n";
    echo '  Quota: '.($resp1['quota'][$devicePhone]['remaining'] ?? 'N/A')." remaining\n";
} else {
    echo "  ❌ FAILED\n";
    echo '  Error: '.($resp1['reason'] ?? 'Unknown')."\n";
}

echo "\nTest 2 (Device Token):\n";
if ($resp2['status'] ?? false) {
    echo "  ✅ SUCCESS\n";
    echo '  Detail: '.($resp2['detail'] ?? 'N/A')."\n";
    echo '  Process: '.($resp2['process'] ?? 'N/A')."\n";
    echo '  Message ID: '.($resp2['id'][0] ?? 'N/A')."\n";
} else {
    echo "  ❌ FAILED\n";
    echo '  Error: '.($resp2['reason'] ?? 'Unknown')."\n";
}

echo "\n========================================\n";
echo "Cek WhatsApp untuk pesan test!\n";
echo "Jika pesan diterima, berarti Fonnte API berfungsi.\n";
echo "Jika tidak, masalah di device/WhatsApp connection.\n";
echo "========================================\n";
