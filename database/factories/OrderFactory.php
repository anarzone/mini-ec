<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => strtoupper($this->faker->unique()->bothify('ORD-########')),
            'contract_email' => $this->faker->safeEmail(),
            'contact_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
        ];
    }
}
