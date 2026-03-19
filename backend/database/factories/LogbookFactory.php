<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookFactory extends Factory
{
    public function definition(): array
    {
        // Random locations in Jakarta
        $latStart = fake()->latitude(-6.3, -6.1);
        $lngStart = fake()->longitude(106.7, 106.9);

        $start_kerja = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'user_id' => User::factory(),
            'start_kerja' => $start_kerja,
            'lokasi_start' => json_encode(['lat' => $latStart, 'lng' => $lngStart]),
            'status' => 'DRAFT',
            'end_kerja' => null,
            'lokasi_end' => null,
            'gambar_bukti' => null,
            'rating' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(function (array $attributes) {
            $end_kerja = clone $attributes['start_kerja'];
            $end_kerja->modify('+8 hours');

            $latEnd = fake()->latitude(-6.3, -6.1);
            $lngEnd = fake()->longitude(106.7, 106.9);

            return [
                'status' => 'SUBMITTED',
                'end_kerja' => $end_kerja,
                'lokasi_end' => json_encode(['lat' => $latEnd, 'lng' => $lngEnd]),
                'gambar_bukti' => json_encode([fake()->imageUrl()]),
            ];
        });
    }

    public function reviewed(): static
    {
        return $this->submitted()->state(function (array $attributes) {
            $reviewed_at = clone $attributes['end_kerja'];
            $reviewed_at->modify('+1 day');

            return [
                'status' => 'REVIEWED',
                'rating' => fake()->numberBetween(1, 5),
                'reviewed_by' => User::factory(),
                'reviewed_at' => $reviewed_at,
            ];
        });
    }
}
