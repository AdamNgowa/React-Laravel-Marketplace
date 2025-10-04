<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\RolesEnum;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create only the Admin user
        User::updateOrCreate(
            ['email' => 'adamngowa3@gmail.com'], // prevent duplicates if seeded twice
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        )->assignRole(RolesEnum::Admin->value);
    }
}
