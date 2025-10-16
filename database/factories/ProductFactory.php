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
       $name = $this->faker->unique()->words(3, true);

        return [
            'name'     => ucfirst($name),
            'price'    => $this->faker->randomFloat(2, 20000, 2000000), 
        ];
    }
}
