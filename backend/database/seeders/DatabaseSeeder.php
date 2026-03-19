<?php

namespace Database\Seeders;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'nama' => 'Super Admin Sistem',
            'email' => 'superadmin@logbook.com',
            'npp' => '198001012000011001',
            'role' => 'SuperAdmin',
            'manager_id' => null,
        ]);

        $admin = User::factory()->create([
            'nama' => 'Admin Operasional',
            'email' => 'admin@logbook.com',
            'npp' => '198502022005011002',
            'role' => 'Admin',
            'manager_id' => null,
        ]);

        $staticStaff = User::factory()->create([
            'nama' => 'Staff Siti',
            'email' => 'staff@logbook.com',
            'npp' => '199003032010012003',
            'role' => 'Staff',
            'manager_id' => $admin->id,
        ]);

        /** @var Collection<int, User> $staffs */
        $staffs = collect([$staticStaff]);

        for ($i = 0; $i < 9; $i++) {
            $staffs->push(User::factory()->create([
                'role' => 'Staff',
                'manager_id' => $admin->id,
            ]));
        }

        $this->command?->info('Seeded users: '.collect([$superAdmin, $admin])->count().' elevated + '.$staffs->count().' staff');

        $kpis = KpiMaster::factory()->count(20)->create();

        foreach ($staffs as $staff) {
            $assignedKpis = $kpis->random(rand(3, 5));
            foreach ($assignedKpis as $kpi) {
                UserKpiAssignment::factory()->create([
                    'user_id' => $staff->id,
                    'kpi_id' => $kpi->id,
                    'assigned_by' => $admin->id,
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
                    $startTime = sprintf('%02d:%02d:00', rand(8, 9), rand(0, 59));
                    $logbook = Logbook::factory()->accepted()->create([
                        'user_id' => $staff->id,
                        'tanggal' => $date->toDateString(),
                        'start_kerja' => $startTime,
                        'reviewed_by' => $admin->id,
                    ]);
                } elseif ($statusChoice <= 8) {
                    $startTime = sprintf('%02d:%02d:00', rand(8, 9), rand(0, 59));
                    $logbook = Logbook::factory()->submitted()->create([
                        'user_id' => $staff->id,
                        'tanggal' => $date->toDateString(),
                        'start_kerja' => $startTime,
                    ]);
                } elseif ($statusChoice <= 9) {
                    $startTime = sprintf('%02d:%02d:00', rand(8, 9), rand(0, 59));
                    $logbook = Logbook::factory()->rejected()->create([
                        'user_id' => $staff->id,
                        'tanggal' => $date->toDateString(),
                        'start_kerja' => $startTime,
                        'reviewed_by' => $admin->id,
                    ]);
                } else {
                    continue;
                }

                foreach ($assignedKpis as $kpi) {
                    LogbookKpiDetail::factory()->create([
                        'logbook_id' => $logbook->id,
                        'kpi_id' => $kpi->id,
                        'kpi_nama' => $kpi->nama,
                        'target_angka' => $kpi->target_angka,
                        'satuan' => $kpi->satuan,
                        'capaian_angka' => rand(0, 1) === 1 ? (float) $kpi->target_angka : 0,
                        'finished_at' => in_array($logbook->status, ['ACCEPTED', 'SUBMITTED'], true) ? now()->subMinutes(rand(10, 120)) : null,
                    ]);
                }
            }

            $draftLogbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => Carbon::today()->toDateString(),
                'start_kerja' => '08:30:00',
                'status' => 'DRAFT',
            ]);

            foreach ($assignedKpis as $kpi) {
                LogbookKpiDetail::factory()->create([
                    'logbook_id' => $draftLogbook->id,
                    'kpi_id' => $kpi->id,
                    'kpi_nama' => $kpi->nama,
                    'target_angka' => $kpi->target_angka,
                    'satuan' => $kpi->satuan,
                    'capaian_angka' => 0,
                    'finished_at' => null,
                ]);
            }
        }

        $this->call(MigrateExistingDataSeeder::class);
    }
}
