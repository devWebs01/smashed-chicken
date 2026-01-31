<?php

namespace App\Console\Commands;

use App\Services\FonnteService;
use Illuminate\Console\Command;

class CheckDeviceStatusCommand extends Command
{
    protected $signature = 'fonnte:device-status {phone? : Device phone number}';

    protected $description = 'Check Fonnte device connection status';

    public function __construct(
        private readonly FonnteService $fonnteService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $phone = $this->argument('phone');

        if (! $phone) {
            // Get all devices from database
            $devices = \App\Models\Device::all();

            if ($devices->isEmpty()) {
                $this->error('No devices found in database');

                return Command::FAILURE;
            }

            $this->info('Checking status for all devices...');
            $this->newLine();

            foreach ($devices as $device) {
                $this->checkDevice($device->device, $device->token);
                $this->newLine();
            }

            return Command::SUCCESS;
        }

        // Check specific device
        $device = \App\Models\Device::where('device', $phone)->first();

        if (! $device) {
            $this->error("Device not found: {$phone}");

            return Command::FAILURE;
        }

        return $this->checkDevice($device->device, $device->token);
    }

    private function checkDevice(string $phone, string $token): int
    {
        $this->info("Checking device: {$phone}");

        $result = $this->fonnteService->getDeviceProfile($token);

        if (! $result['status']) {
            $this->error('❌ Device check failed');
            $this->error('Error: '.($result['error'] ?? 'Unknown'));

            return Command::FAILURE;
        }

        $data = $result['data'];

        // Display device info
        $this->table(
            ['Property', 'Value'],
            [
                ['Device', $data['device'] ?? 'N/A'],
                ['Name', $data['name'] ?? 'N/A'],
                ['Status', $data['status'] ?? 'N/A'],
                ['Type', $data['type'] ?? 'N/A'],
                ['Expired', $data['expired'] ?? 'N/A'],
                ['Package', $data['package'] ?? 'N/A'],
                ['Quota', $data['quota'] ?? 'N/A'],
                ['Whatsapp', $data['whatsapp'] ?? 'N/A'],
            ]
        );

        // Check connection status
        $status = $data['status'] ?? 'unknown';
        $isConnected = false;

        // Fonnte uses numeric status codes: 1 = connected, 0 = disconnected
        if (is_numeric($status)) {
            $isConnected = (int) $status === 1;
        } else {
            $isConnected = in_array($status, ['connected', 'connect', 'active']);
        }

        $this->newLine();
        $this->info('Device Status Analysis:');
        $this->line("  Raw Status Value: {$status}");
        $this->line('  Type: '.gettype($status));
        $this->newLine();

        if ($isConnected) {
            $this->info('✅ Device reports as CONNECTED to Fonnte');
            $this->warn('⚠️  However, WhatsApp may not be connected!');
            $this->newLine();
            $this->line('The device is registered with Fonnte, but messages show "pending".');
            $this->line('This usually means:');
            $this->line('  1. WhatsApp app is not open on the phone');
            $this->line('  2. WhatsApp Web session is disconnected');
            $this->line('  3. Phone has no internet connection');
            $this->newLine();
            $this->warn('To fix:');
            $this->line('  • Open WhatsApp on your phone (6285951572182)');
            $this->line('  • Keep WhatsApp app active/running');
            $this->line('  • Ensure stable internet connection');
        } else {
            $this->error('❌ Device is DISCONNECTED from Fonnte');
            $this->warn('Messages will remain in PENDING status until device is reconnected');
            $this->newLine();
            $this->warn('To fix:');
            $this->line('  1. Go to Fonnte Dashboard → Device');
            $this->line('  2. Click Connect/Reconnect button');
            $this->line('  3. Scan QR code with WhatsApp');
        }

        return Command::SUCCESS;
    }
}
