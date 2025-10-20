<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code'   => null, 
            'store_id'     => Store::factory(),
            'order_status' => $this->faker->randomElement(['pending','paid','shipping','completed','canceled']),
            'total_amount' => 0,
            'note'         => $this->faker->optional()->sentence(),
        ];
    }
}
