<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['INFO', 'WARNING', 'SUCCESS', 'ERROR']),
            'reference_id' => fake()->uuid(),
            'is_read' => fake()->boolean(),
            'read_at' => fake()->optional()->dateTime(),
        ];
    }
}
