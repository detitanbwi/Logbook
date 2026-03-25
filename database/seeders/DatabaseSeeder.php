<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kpi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Default KPIs [FASE 2]
        $kpi1 = Kpi::create([
            'description' => 'Membangun Infrastruktur Sistem Skalabilitas Tinggi',
            'target' => 100,
            'unit' => 'Persen',
        ]);
        $kpi2 = Kpi::create([
            'description' => 'Efisiensi Operasional Modul HR',
            'target' => 85,
            'unit' => 'Persen',
        ]);
        $kpi3 = Kpi::create([
            'description' => 'Kedisiplinan & Integritas Profesional',
            'target' => 10,
            'unit' => 'Poin',
        ]);

        $superAdmin = User::create([
            'npp' => '20260001',
            'nama' => 'Pimpinan Utama',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status_perkawinan' => 'menikah',
        ]);

        // Assign KPIs to Super Admin
        $superAdmin->kpis()->attach([$kpi1->id, $kpi2->id, $kpi3->id]);

        User::factory(10)->create()->each(function (User $u) use ($kpi1) {
            $u->kpis()->attach([$kpi1->id]);
        });
    }
}
