<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions and roles
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        // Generate permissions based on existing resources
        $this->generatePermissions();

        // Create roles and assign permissions
        $this->createRolesAndAssignPermissions();

        $this->command->info('Shield Seeding Completed.');
        $this->command->info('Created roles: pemilik (super_admin), kasir');
    }

    protected function generatePermissions(): void
    {
        // Manual create permissions karena shield:generate tidak menyimpan ke database
        $resources = [
            'Product' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'replicate', 'reorder'],
            'Order' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'replicate', 'reorder'],
            'User' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'replicate', 'reorder'],
            'Role' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'replicate', 'reorder'],
            'Device' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'replicate', 'reorder'],
        ];

        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action}:{$resource}";
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // Page permissions
        Permission::firstOrCreate(['name' => 'view:Dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view:Reports', 'guard_name' => 'web']);
    }

    protected function createRolesAndAssignPermissions(): void
    {
        // Pemilik (Owner) - sebagai super_admin yang bisa mengelola semuanya
        $pemilik = Role::firstOrCreate(['name' => 'pemilik', 'guard_name' => 'web']);

        // Pemilik mendapatkan semua permissions
        $pemilik->syncPermissions(Permission::all());

        // Kasir (Cashier) - bisa mengelola produk, pemesanan, dan laporan
        $kasir = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        // Kasir permissions - menggunakan format yang sudah digenerate oleh shield
        $kasirPermissions = [
            // Product Management - full access untuk mengelola produk
            'viewAny:Product',
            'view:Product',
            'create:Product',
            'update:Product',
            'delete:Product',
            'replicate:Product',
            'reorder:Product',

            // Order Management - full access untuk mengelola pemesanan
            'viewAny:Order',
            'view:Order',
            'create:Order',
            'update:Order',
            'delete:Order',
            'replicate:Order',
            'reorder:Order',

            // Report permissions - bisa melihat laporan
            'view:Dashboard',
            'view:Reports',

            // View users (hanya view, tidak bisa edit/hapus)
            'viewAny:User',
            'view:User',
        ];

        // Sync permissions untuk kasir
        $kasir->syncPermissions($kasirPermissions);
    }
}