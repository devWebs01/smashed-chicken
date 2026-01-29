<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add the device from Fonnte API
        Device::create([
            'device' => '6285951572182',
            'name' => 'aku xl',
            'token' => 'vDEZZShCRk4PUMGfLHr3',
            'is_active' => true,
        ]);

        $this->command->info('Device added: 6285951572182 - aku xl');
    }
}
