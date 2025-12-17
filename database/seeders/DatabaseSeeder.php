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

        // Create super_admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'pemilik@testing.com',
            'password' => bcrypt('password'), // Default password: password
        ]);
        $superAdmin->assignRole('super_admin');

        // Create kasir (cashier) user
        $kasir = User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@testing.com',
            'password' => bcrypt('password'), // Default password: password
        ]);
        $kasir->assignRole('kasir');

        $this->command->info('Users created:');
        $this->command->info('1. Super Admin - Email: pemilik@testing.com, Password: password');
        $this->command->info('2. Kasir (Cashier) - Email: kasir@testing.com, Password: password');
    }
}
