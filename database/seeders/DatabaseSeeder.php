<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
            DeviceSeeder::class,
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        // Create super_admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'pemilik@testing.com',
            'password' => bcrypt('password'), // Default password: password
        ]);
        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Create kasir (cashier) user
        $kasir = User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@testing.com',
            'password' => bcrypt('password'), // Default password: password
        ]);

        $kasirRole = Role::firstOrCreate([
            'name' => 'kasir',
            'guard_name' => 'web',
        ]);

        if (! $kasir->hasRole('kasir')) {
            $kasir->assignRole($kasirRole);
        }

        $this->command->info('Users created:');
        $this->command->info('1. Super Admin - Email: pemilik@testing.com, Password: password');
        $this->command->info('2. Kasir (Cashier) - Email: kasir@testing.com, Password: password');
    }
}
