<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        return [
            'type' => 'addon',
            'slug' => fake()->unique()->slug(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(29000, 199000),
            'original_price' => fake()->optional(0.6)->numberBetween(200000, 350000),
            'is_active' => true,
            'metadata' => [
                'is_recurring' => false,
                'recurring_interval' => null,
            ],
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is recurring.
     */
    public function recurring(string $interval = 'year'): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'is_recurring' => true,
                'recurring_interval' => $interval,
            ],
        ]);
    }

    /**
     * Create base package product.
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'base_package',
            'slug' => 'base',
            'name' => 'Paket Undangan Seumur Hidup',
            'description' => 'Undangan digital dengan fitur lengkap, sekali bayar seumur hidup',
            'price' => 99000,
            'metadata' => [
                'is_recurring' => false,
                'recurring_interval' => null,
            ],
        ]);
    }
}
