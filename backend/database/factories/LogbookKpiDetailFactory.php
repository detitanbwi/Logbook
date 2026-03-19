<?php

namespace Database\Factories;

use App\Models\KpiMaster;
use App\Models\Logbook;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookKpiDetailFactory extends Factory
{
    public function definition(): array
    {
        $targetAngka = fake()->randomFloat(2, 1, 100);

        return [
            'logbook_id' => Logbook::factory(),
            'kpi_id' => KpiMaster::factory(),
            'kpi_nama' => fake('id_ID')->sentence(),
            'target_angka' => $targetAngka,
            'satuan' => fake()->randomElement(['dokumen', 'jam', 'unit', 'laporan', 'tiket']),
            'capaian_angka' => fake()->randomFloat(2, 0, $targetAngka),
            'lampiran_file' => fake()->optional()->lexify('lampiran-??????.pdf'),
            'finished_at' => fake()->optional()->dateTime(),
        ];
    }
}
