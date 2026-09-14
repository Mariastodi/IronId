<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'method' => 'face',
            'match_distance' => fake()->randomFloat(4, 0.1, 0.45),
            'checked_in_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
