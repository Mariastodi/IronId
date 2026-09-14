<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Mensal', 'Trimestral', 'Semestral', 'Anual']),
            'price' => fake()->randomFloat(2, 79, 349),
            'duration_days' => fake()->randomElement([30, 90, 180, 365]),
            'description' => fake()->sentence(),
            'active' => true,
        ];
    }
}
