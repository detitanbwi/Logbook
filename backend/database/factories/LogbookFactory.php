<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookFactory extends Factory
{
    public function definition(): array
    {
        $tanggal = fake()->dateTimeBetween('-1 month', 'now');
        $startHour = fake()->numberBetween(7, 10);
        $startMinute = fake()->numberBetween(0, 59);
        $startKerja = sprintf('%02d:%02d:00', $startHour, $startMinute);
        $endHour = min($startHour + 8, 23);
        $endKerja = sprintf('%02d:%02d:00', $endHour, $startMinute);

        return [
            'user_id' => User::factory(),
            'tanggal' => $tanggal->format('Y-m-d'),
            'start_kerja' => $startKerja,
            'lokasi' => 'Jakarta Selatan',
            'lokasi_lat' => fake()->latitude(-6.3, -6.1),
            'lokasi_lng' => fake()->longitude(106.7, 106.9),
            'status' => 'SUBMITTED',
            'end_kerja' => $endKerja,
            'rating' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'reviewer_comment' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(function (array $attributes) {
            $start = \DateTimeImmutable::createFromFormat('H:i:s', (string) $attributes['start_kerja']) ?: new \DateTimeImmutable('08:00:00');
            $end = $start->modify('+8 hours')->format('H:i:s');

            return [
                'status' => 'SUBMITTED',
                'end_kerja' => $end,
            ];
        });
    }

    public function accepted(): static
    {
        return $this->submitted()->state(function (array $attributes) {
            $tanggal = (string) ($attributes['tanggal'] ?? fake()->date());
            $endKerja = (string) ($attributes['end_kerja'] ?? '17:00:00');

            return [
                'status' => 'ACCEPTED',
                'rating' => fake()->numberBetween(1, 5),
                'reviewed_by' => User::factory(),
                'reviewed_at' => "{$tanggal} {$endKerja}",
                'reviewer_comment' => fake('id_ID')->sentence(),
            ];
        });
    }

    public function rejected(): static
    {
        return $this->submitted()->state(function (array $attributes) {
            $tanggal = (string) ($attributes['tanggal'] ?? fake()->date());
            $endKerja = (string) ($attributes['end_kerja'] ?? '17:00:00');

            return [
                'status' => 'REJECTED',
                'reviewed_by' => User::factory(),
                'reviewed_at' => "{$tanggal} {$endKerja}",
                'reviewer_comment' => fake('id_ID')->sentence(),
            ];
        });
    }
}
