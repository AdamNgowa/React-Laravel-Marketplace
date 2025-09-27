<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\User;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // If no user_id is passed, it will create a new User automatically
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement([
                VendorStatusEnum::Pending->value,
                VendorStatusEnum::Approved->value,
                VendorStatusEnum::Rejected->value,
            ]),
            'store_name' => $this->faker->company(),
            'store_address' => $this->faker->address(),
            'cover_image' => null,
        ];
    }
}
