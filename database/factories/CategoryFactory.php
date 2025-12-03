<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(95),
        ];
    }
}
