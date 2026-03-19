<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'table_name' => fake()->word(),
            'record_id' => fake()->uuid(),
            'action' => fake()->randomElement(['CREATE', 'UPDATE', 'DELETE']),
            'old_data' => json_encode(['key' => 'old_value']),
            'new_data' => json_encode(['key' => 'new_value']),
            'performed_by' => User::factory(),
            'performed_at' => now(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
