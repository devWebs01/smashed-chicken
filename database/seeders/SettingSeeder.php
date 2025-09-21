<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageContents = file_get_contents('https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/50c3438b-c1b3-451e-ac7d-032ab5d5f8cb_Go-Food-Merchant_20250603_014347.jpeg?auto=format');
        if ($imageContents === false) {
            throw new \Exception('Could not get contents from URL.');
        }
        $imageName = Str::random(20) . '.jpg';
        $imagePath = 'setting/' . $imageName;
        Storage::disk('public')->put($imagePath, $imageContents);

        Log::info('Image for Ayam Geprek Mother saved to ' . $imagePath);
        Setting::create([
            'name' => 'Ayam Geprek Mother',
            'logo' => $imagePath,
            'address' => '123 Main St, Anytown, USA',
            'phone' => '555-1234',
        ]);
    }
}
