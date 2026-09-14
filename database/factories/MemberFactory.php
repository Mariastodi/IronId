<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'name' => fake()->name(),
            'matricula' => 'ID-'.now()->format('y').'-'.Str::padLeft((string) fake()->unique()->numberBetween(1, 99999), 5, '0'),
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(##) #####-####'),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-16 years'),
            'plan_id' => Plan::factory(),
            'plan_started_at' => $startedAt,
            'plan_expires_at' => fake()->dateTimeBetween($startedAt, '+2 months'),
            'active' => true,
        ];
    }
}
