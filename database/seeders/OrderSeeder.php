<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 orders with items
        Order::factory()
            ->count(10)
            ->withItems(3) // Each order will have 3 items
            ->create();

        // Create 5 completed orders with items
        Order::factory()
            ->count(5)
            ->completed()
            ->withItems(2)
            ->create();

        // Create 3 pending delivery orders
        Order::factory()
            ->count(3)
            ->pending()
            ->delivery()
            ->withItems(4)
            ->create();

        // Create 2 dine-in orders
        Order::factory()
            ->count(2)
            ->processing()
            ->dineIn()
            ->withItems(2)
            ->create();
    }
}