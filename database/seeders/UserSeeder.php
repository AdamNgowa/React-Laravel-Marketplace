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
        // Admin only
        User::factory()->create([
            'name' => 'Adam',
            'email' => 'adamngowa3@gmail.com',
            'password' => Hash::make('12345678'),
        ])->assignRole(RolesEnum::Admin->value);
    }
}
