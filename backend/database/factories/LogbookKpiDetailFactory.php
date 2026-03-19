<?php

namespace Database\Factories;

use App\Models\KpiMaster;
use App\Models\Logbook;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookKpiDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'logbook_id' => Logbook::factory(),
            'kpi_id' => KpiMaster::factory(),
            'kpi_nama' => fake('id_ID')->sentence(),
            'is_finished' => fake()->boolean(),
            'finished_at' => fake()->optional()->dateTime(),
        ];
    }
}
