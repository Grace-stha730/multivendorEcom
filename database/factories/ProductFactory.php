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
        return [
            'name' => $this->faker->name(),
            'price' => $this->faker->numberBetween(100, 1000),
            'stock' => $this->faker->numberBetween(0, 50),
            'summary' => $this->faker->sentence(10), // Short summary
            'description' => $this->faker->sentence(50), // Short summary
            'discount' => $this->faker->numberBetween(0, 30),
            'discount_amount' => $this->faker->numberBetween(0, 100),
            'category_id' => 1,
            'vendor_id' =>1,
        ];
    }
}
