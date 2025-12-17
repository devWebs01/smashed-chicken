<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run ShieldSeeder first to create roles and permissions
        $this->call([
            ShieldSeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
        ]);

        // Create pemilik (owner) user - sebagai super_admin
        $pemilik = User::factory()->create([
            'name' => 'Pemilik',
            'email' => 'pemilik@geprek.com',
            'password' => bcrypt('password'), // Default password: password
        ]);
        $pemilik->assignRole('pemilik');

        // Create kasir (cashier) user
        $kasir = User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@geprek.com',
            'password' => bcrypt('password'), // Default password: password
        ]);
        $kasir->assignRole('kasir');

        $this->command->info('Users created:');
        $this->command->info('1. Pemilik (Owner) - Email: pemilik@geprek.com, Password: password');
        $this->command->info('2. Kasir (Cashier) - Email: kasir@geprek.com, Password: password');
    }
}
