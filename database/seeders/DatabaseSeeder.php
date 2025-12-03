<?php

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding categories...');

        // Create main categories
        $dressCategory = Category::factory()->create(['name' => 'Dresses', 'slug' => 'dresses']);
        $laptopCategory = Category::factory()->create(['name' => 'Laptops', 'slug' => 'laptops']);
        $coffeeMachineCategory = Category::factory()->create(['name' => 'Coffee Machines', 'slug' => 'coffee-machines']);

        // Create additional random categories
        Category::factory(17)->create();

        $this->command->info('Seeding products...');

        // Seed dresses
        Product::factory(400)->dress()->hasAttached($dressCategory)->create()
            ->each(function ($product) {
                ProductVariant::factory(rand(1, 3))->forDress()->create([
                    'product_id' => $product->id,
                ]);
            });

        // Seed laptops
        Product::factory(300)->laptop()->hasAttached($laptopCategory)->create()
            ->each(function ($product) {
                ProductVariant::factory(rand(1, 2))->forLaptop()->create([
                    'product_id' => $product->id,
                ]);
            });

        // Seed coffee machines
        Product::factory(300)->coffeeMachine()->hasAttached($coffeeMachineCategory)->create()
            ->each(function ($product) {
                ProductVariant::factory(rand(1, 2))->forCoffeeMachine()->create([
                    'product_id' => $product->id,
                ]);
            });

        $this->command->info('Seeding customers...');
        Customer::factory(50)->create();

        $this->command->info('Seeding orders...');

        for ($i = 0; $i < 3000; $i++) {
            // Create billing and shipping addresses for each order
            $billingAddress = Address::factory()->create(['type' => AddressType::Billing]);
            $shippingAddress = Address::factory()->create(['type' => AddressType::Shipping]);

            Order::factory()->create([
                'billing_address_id' => $billingAddress->id,
                'shipping_address_id' => $shippingAddress->id,
            ]);

            if ($i % 1000 === 0 && $i > 0) {
                $this->command->info("Created $i orders...");
            }
        }

        $this->command->info('Seeding completed!');
    }
}
