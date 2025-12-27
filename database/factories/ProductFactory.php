<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productTypes = ['Laptop', 'Smartphone', 'Headphones', 'Keyboard', 'Mouse', 'Monitor', 'Tablet', 'Camera', 'Speaker', 'Watch', 'Charger', 'Cable', 'Case', 'Stand', 'Adapter'];
        $adjectives = ['Premium', 'Pro', 'Ultra', 'Wireless', 'Bluetooth', 'USB-C', 'Fast', 'Portable', 'Compact', 'Ergonomic'];
        
        return [
            'name' => fake()->randomElement($adjectives) . ' ' . fake()->randomElement($productTypes) . ' ' . fake()->numberBetween(1, 5),
            'price' => fake()->randomFloat(2, 9.99, 999.99),
            'stock_quantity' => fake()->numberBetween(0, 500),
        ];
    }
}
