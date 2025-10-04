<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Enums\RolesEnum;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles with guard_name 'web'
        $userRole = Role::firstOrCreate(['name' => RolesEnum::User->value, 'guard_name' => 'web']);
        $vendorRole = Role::firstOrCreate(['name' => RolesEnum::Vendor->value, 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => RolesEnum::Admin->value, 'guard_name' => 'web']);

        // Create permissions with guard_name 'web'
        $approveVendors = Permission::firstOrCreate(['name' => PermissionsEnum::ApproveVendors->value, 'guard_name' => 'web']);
        $sellProducts   = Permission::firstOrCreate(['name' => PermissionsEnum::SellProducts->value, 'guard_name' => 'web']);
        $buyProducts    = Permission::firstOrCreate(['name' => PermissionsEnum::BuyProducts->value, 'guard_name' => 'web']);

        // Assign permissions to roles
        $userRole->syncPermissions([$buyProducts]);

        $vendorRole->syncPermissions([
            $buyProducts,
            $sellProducts
        ]);

        $adminRole->syncPermissions([
            $buyProducts,
            $sellProducts,
            $approveVendors, // <-- you had sellProducts twice, fixed
        ]);
    }
}
