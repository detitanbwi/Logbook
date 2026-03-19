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
        // 18 digits NPP
        $npp = $this->faker->numberBetween(1970, 2000).
               str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT).
               str_pad($this->faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT).
               $this->faker->numberBetween(2000, 2023).
               str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT).
               $this->faker->numberBetween(1, 2).
               str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

        return [
            'nama' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'npp' => $npp,
            'role' => 'Staff',
            'manager_id' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'last_password_change' => now(),
            'foto' => null,
            'tempat_lahir' => fake('id_ID')->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-55 years', '-20 years')->format('Y-m-d'),
            'nik' => fake()->numerify('################'),
            'npwp' => fake()->numerify('##.###.###.#-###.###'),
            'alamat' => fake('id_ID')->address(),
            'status_kawin' => fake()->randomElement(['Belum_Kawin', 'Kawin', 'Cerai_Hidup', 'Cerai_Mati']),
            'riwayat_pendidikan' => [
                [
                    'jenjang' => fake()->randomElement(['SMA', 'D3', 'S1', 'S2']),
                    'jurusan' => fake('id_ID')->word(),
                    'institusi' => fake('id_ID')->company(),
                    'tahun' => (int) fake()->year(),
                ],
            ],
            'riwayat_karir' => [
                [
                    'jabatan' => fake('id_ID')->jobTitle(),
                    'perusahaan' => fake('id_ID')->company(),
                    'periode' => fake()->year().'-'.fake()->year(),
                ],
            ],
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
