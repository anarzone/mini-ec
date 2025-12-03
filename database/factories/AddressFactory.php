<?php

namespace Database\Factories;

use App\Enums\AddressType;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(AddressType::cases()),
            'address_data' => [
                'line1' => $this->faker->streetAddress(),
                'line2' => $this->faker->optional()->secondaryAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'zip' => $this->faker->postcode(),
            ],
            'is_default' => false,
        ];
    }
}
