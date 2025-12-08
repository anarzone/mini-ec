<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    private array $dressBrands = [
        'Zara', 'H&M', 'Gucci', 'Prada', 'Versace', 'Chanel',
        'Dior', 'Armani', 'Calvin Klein', 'Michael Kors', 'Ralph Lauren'
    ];

    private array $laptopBrands = [
        'Apple', 'Dell', 'HP', 'Lenovo', 'ASUS', 'Acer',
        'MSI', 'Razer', 'Microsoft', 'Samsung', 'LG'
    ];

    private array $coffeeMachineBrands = [
        'Breville', 'Nespresso', 'Keurig', 'Cuisinart',
        'Mr. Coffee', 'Ninja', 'Hamilton Beach', 'Krups', 'Philips'
    ];

    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        return [
            'title' => ucfirst($title),
            'description' => $this->faker->paragraph(),
            'slug' => Str::slug($title),
            'brand' => $this->faker->company(),
            'attributes' => [
                'color' => $this->faker->safeColorName(),
                'size' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL']),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function dress(): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $this->faker->randomElement($this->dressBrands),
            'attributes' => [
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
                'pattern' => $this->faker->randomElement(['Floral', 'Striped', 'Solid', 'Polka Dot']),
                'sleeve_length' => $this->faker->randomElement(['Short', 'Long', 'Sleeveless']),
                'dress_length' => $this->faker->randomElement(['Mini', 'Knee-length', 'Midi', 'Maxi']),
            ],
        ]);
    }

    public function laptop(): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $this->faker->randomElement($this->laptopBrands),
            'attributes' => [
                'processor' => $this->faker->randomElement(['Intel i5', 'Intel i7', 'Intel i9', 'AMD Ryzen 5', 'AMD Ryzen 7', 'M3 Pro']),
                'screen_size' => $this->faker->randomElement(['13 inch', '14 inch', '15 inch', '16 inch', '17 inch']),
                'graphics_card' => $this->faker->randomElement(['Integrated', 'NVIDIA RTX 3060', 'NVIDIA RTX 4060', 'AMD Radeon']),
                'operating_system' => $this->faker->randomElement(['Windows 11', 'macOS', 'Linux']),
                'screen_resolution' => $this->faker->randomElement(['1920x1080', '2560x1440', '3840x2160']),
            ],
        ]);
    }

    public function coffeeMachine(): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $this->faker->randomElement($this->coffeeMachineBrands),
            'attributes' => [
                'capacity_cups' => $this->faker->numberBetween(4, 12),
                'power_watts' => $this->faker->randomElement([800, 1000, 1200, 1500]),
                'machine_type' => $this->faker->randomElement(['Espresso', 'Drip', 'French Press', 'Pod']),
                'programmable' => $this->faker->boolean(),
                'grinder_included' => $this->faker->boolean(),
                'milk_frother' => $this->faker->boolean(),
            ],
        ]);
    }
}
