<?php

namespace Database\Factories;

use App\Models\KpiMaster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserKpiAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kpi_id' => KpiMaster::factory(),
            'assigned_by' => User::factory(),
        ];
    }
}
