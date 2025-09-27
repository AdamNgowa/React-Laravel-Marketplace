<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Normal User
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
        ])->assignRole(RolesEnum::User->value);

        // Vendor (already approved)
        $user = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $user->assignRole(RolesEnum::Vendor->value);

        Vendor::factory()->create([
            'user_id' => $user->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor Store',
            'store_address' => fake()->address(),
        ]);

        // Admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
        ])->assignRole(RolesEnum::Admin->value);
    }
}
