<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-????')),
            'price_cents' => $this->faker->numberBetween(1000, 50000),
            'currency' => 'USD',
            'weight_g' => $this->faker->numberBetween(100, 5000),
            'is_active' => $this->faker->boolean(90),
            'attributes' => [],
        ];
    }

    public function forDress(): static
    {
        return $this->state(fn (array $attributes) => [
            'attributes' => [
                'color' => $this->faker->randomElement(['Red', 'Blue', 'Black', 'White', 'Green', 'Yellow']),
                'size' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            ],
            'weight_g' => $this->faker->numberBetween(200, 800),
        ]);
    }

    public function forLaptop(): static
    {
        return $this->state(fn (array $attributes) => [
            'attributes' => [
                'color' => $this->faker->randomElement(['Space Gray', 'Silver', 'Black', 'White']),
                'ram_gb' => $this->faker->randomElement([8, 16, 32, 64]),
                'storage_gb' => $this->faker->randomElement([256, 512, 1024, 2048]),
            ],
            'price_cents' => $this->faker->numberBetween(50000, 300000),
            'weight_g' => $this->faker->numberBetween(1200, 2500),
        ]);
    }

    public function forCoffeeMachine(): static
    {
        return $this->state(fn (array $attributes) => [
            'attributes' => [
                'color' => $this->faker->randomElement(['Black', 'Silver', 'Red', 'White']),
            ],
            'price_cents' => $this->faker->numberBetween(5000, 50000),
            'weight_g' => $this->faker->numberBetween(2000, 8000),
        ]);
    }
}
