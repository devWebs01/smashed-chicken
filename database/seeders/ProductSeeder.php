<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Ayam geprek',
                'price' => 17500,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/647c153a-0aad-42a9-a18c-d0ab0f5d609b_ded6c5f8-e1cf-4550-8d51-c60ff7b9732c.jpg?auto=format',
            ],
            [
                'name' => 'Ayam geprek + nasi biasa',
                'price' => 21500,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/64d98db6-f3d0-48f8-905c-b225f1ccc9bd_a4e4658a-84e8-4d43-bc6a-b1d222210c67_Go-Resto_20181107_153141.jpg?auto=format',
            ],
            [
                'name' => 'Ayam geprek + nasi uduk',
                'price' => 24000,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/ad2202ab-2610-48ae-8403-aa35546325d2_eb96fb00-ed59-4a17-9e6f-61050ad4f58e_Go-Resto_20181107_153208.jpg?auto=format',
            ],
            [
                'name' => 'Ayam Geprek + Nasi Biasa+ Tahu tempe',
                'price' => 25000,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/ad3bcc1e-6cba-42aa-ae73-d8061feeac77_cffd9a30-5c3b-4795-8ec9-29d0ce7f4416_Go-Biz_20200117_133234.jpeg?auto=format',
            ],
            [
                'name' => 'Ayam Geprek + Nasi Uduk+tahu Tempe',
                'price' => 27500,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/28157cae-0260-48ad-8403-a5967e23efff_865b784e-f0ae-4e39-99da-7c371bfff6ab_Go-Biz_20200117_133215.jpeg?auto=format',
            ],
            [
                'name' => 'Ayam Geprek Mozarella',
                'price' => 30000,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-id/v2/images/uploads/c595bfb0-9cd4-4a99-ba72-0058e6a2f068_Image-+-Badge.png',
            ],
            [
                'name' => 'Ayam Gep mozarela + nasi biasa',
                'price' => 34000,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/525f1429-01ba-4be3-a572-29ac3d879db5_19fcd606-bc38-4c68-9899-515ac7301968_Go-Resto_20181106_230320.jpg?auto=format',
            ],
            [
                'name' => 'Ayam Gep mozarela + nasi uduk',
                'price' => 36500,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-indonesia/v2/images/uploads/6fb3cdd7-0804-4211-946e-6540359ec0b9_32c2d8c2-c16a-4c2c-80e5-8e5a88c8ac8e_Go-Resto_20181106_230246.jpg?auto=format',
            ],
            [
                'name' => 'Ayam Gep Moza + Tahu Tempe + Nasi Biasa',
                'price' => 37500,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-id/v2/images/uploads/c595bfb0-9cd4-4a99-ba72-0058e6a2f068_Image-+-Badge.png',
            ],
            [
                'name' => 'Ayam Gep Moza + Tahu Tempe + Nasi Uduk',
                'price' => 40000,
                'image_url' => 'https://i.gojekapi.com/darkroom/gofood-id/v2/images/uploads/c595bfb0-9cd4-4a99-ba72-0058e6a2f068_Image-+-Badge.png',
            ],
        ];

        foreach ($products as $data) {
            try {
                $imageContents = file_get_contents($data['image_url']);
                if ($imageContents === false) {
                    throw new \Exception("Could not get contents from URL.");
                }
                $imageName = Str::random(20) . '.jpg';
                $imagePath = 'products/' . $imageName;
                Storage::disk('public')->put($imagePath, $imageContents);
                Log::info('Image for ' . $data['name'] . ' saved to ' . $imagePath);
                 Product::create([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'description' => $data['name'],
                    'image' => $imagePath,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to download image for ' . $data['name'] . ' from ' . $data['image_url'] . '. Error: ' . $e->getMessage());
                Product::create([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'description' => $data['name'],
                    'image' => null,
                ]);
            }
        }
    }
}