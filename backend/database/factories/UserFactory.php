<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // 18 digits NIP
        $nip = $this->faker->numberBetween(1970, 2000).
               str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT).
               str_pad($this->faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT).
               $this->faker->numberBetween(2000, 2023).
               str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT).
               $this->faker->numberBetween(1, 2).
               str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

        return [
            'name' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'nip' => $nip,
            'role' => 'STAFF',
            'manager_id' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'last_password_change' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
