<?php

namespace Database\Seeders;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Static Accounts for UI Development & Testing
        $admin = User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@logbook.com',
            'nip' => '198001012000011001',
            'role' => 'ADMIN',
        ]);

        $staticManager = User::factory()->create([
            'name' => 'Manager Budi',
            'email' => 'manager@logbook.com',
            'nip' => '198502022005011002',
            'role' => 'MANAGER',
            'manager_id' => null,
        ]);

        $staticStaff = User::factory()->create([
            'name' => 'Staff Siti',
            'email' => 'staff@logbook.com',
            'nip' => '199003032010012003',
            'role' => 'STAFF',
            'manager_id' => $staticManager->id,
        ]);

        // 2. Random Accounts for Volume
        $managers = User::factory()->count(2)->create([
            'role' => 'MANAGER',
            'manager_id' => null,
        ]);
        $managers->push($staticManager);

        $staffs = collect([$staticStaff]);
        for ($i = 0; $i < 9; $i++) {
            $staffs->push(User::factory()->create([
                'role' => 'STAFF',
                'manager_id' => $managers[$i % 3]->id,
            ]));
        }

        // 3. Master Data
        $kpis = KpiMaster::factory()->count(20)->create();

        // 4. Assign KPIs and generate Logbook History
        foreach ($staffs as $staff) {
            $assignedKpis = $kpis->random(rand(3, 5));
            foreach ($assignedKpis as $kpi) {
                UserKpiAssignment::factory()->create([
                    'user_id' => $staff->id,
                    'kpi_id' => $kpi->id,
                    'assigned_by' => $staff->manager_id,
                ]);
            }

            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now()->subDays(1);

            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend() && rand(0, 1) === 0) {
                    continue;
                }

                $statusChoice = rand(1, 10);

                if ($statusChoice <= 6) {
                    $logbook = Logbook::factory()->reviewed()->create([
                        'user_id' => $staff->id,
                        'start_kerja' => $date->copy()->setHour(rand(8, 9))->setMinute(rand(0, 59)),
                        'reviewed_by' => $staff->manager_id,
                    ]);
                } elseif ($statusChoice <= 8) {
                    $logbook = Logbook::factory()->submitted()->create([
                        'user_id' => $staff->id,
                        'start_kerja' => $date->copy()->setHour(rand(8, 9))->setMinute(rand(0, 59)),
                    ]);
                } else {
                    continue;
                }

                foreach ($assignedKpis as $kpi) {
                    LogbookKpiDetail::factory()->create([
                        'logbook_id' => $logbook->id,
                        'kpi_id' => $kpi->id,
                        'kpi_nama' => $kpi->nama,
                        'is_finished' => rand(0, 1) === 1,
                        'finished_at' => ($logbook->status === 'REVIEWED' || $logbook->status === 'SUBMITTED') ? $logbook->end_kerja->copy()->subMinutes(rand(10, 120)) : null,
                    ]);
                }
            }

            // Generate today's draft
            $draftLogbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'start_kerja' => Carbon::today()->setHour(8)->setMinute(30),
                'status' => 'DRAFT',
            ]);

            foreach ($assignedKpis as $kpi) {
                LogbookKpiDetail::factory()->create([
                    'logbook_id' => $draftLogbook->id,
                    'kpi_id' => $kpi->id,
                    'kpi_nama' => $kpi->nama,
                    'is_finished' => false,
                    'finished_at' => null,
                ]);
            }
        }
    }
}
