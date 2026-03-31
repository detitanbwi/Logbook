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

        // --- START CUSTOM SEEDER FOR STAFF HIERARCHY ---
        
        $staffMembers = [];
        $roles = ['staff', 'admin', 'manager'];
        $nppStart = 20260002;

        for ($i = 0; $i < 10; $i++) {
            $currentUserNpp = (string)($nppStart + $i);
            
            // Logic for boss: 
            // First user has Super Admin as boss.
            // Subsequent users use User 2 or 3 (index 0 or 1 in $staffMembers) as boss randomly.
            $bossId = $superAdmin->id;
            if (count($staffMembers) >= 2) {
                // Randomly pick index 0 or 1 as the boss
                $bossIndex = rand(0, 1);
                $bossId = $staffMembers[$bossIndex]->id;
            }

            $user = User::create([
                'npp' => $currentUserNpp,
                'nama' => 'Karyawan ' . ($i + 1),
                'password' => Hash::make('password'),
                'role' => $roles[array_rand($roles)],
                'status_perkawinan' => 'menikah',
                'supervisor_id' => $bossId, // Fix: Changed from atasan_id to supervisor_id
            ]);

            $user->kpis()->attach([$kpi1->id, $kpi2->id, $kpi3->id]);
            $staffMembers[] = $user;
        }

        // --- END CUSTOM SEEDER ---
    }
}
