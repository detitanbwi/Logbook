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
        // Password = password
        // List User
        // Super Admin 198001012000011001
        // Admin 198502022005011002
        // StaffLead 199003032010012003
        // StaffNormal 199204142012012004

        $superAdmin = User::factory()->create([
            "nama" => "Super Admin Sistem",
            "email" => "superadmin@logbook.com",
            "npp" => "198001012000011001",
            "role" => "SuperAdmin",
            "manager_id" => null,
        ]);

        $admin = User::factory()->create([
            "nama" => "Admin Operasional",
            "email" => "admin@logbook.com",
            "npp" => "198502022005011002",
            "role" => "Admin",
            "manager_id" => null,
        ]);

        $staffLead = User::factory()->create([
            "nama" => "Staff Koordinator Lapangan",
            "email" => "staff.lead@logbook.com",
            "npp" => "199003032010012003",
            "role" => "Staff",
            "manager_id" => $admin->id,
        ]);

        $staffSubordinate = User::factory()->create([
            "nama" => "Staff Pelaksana Lapangan",
            "email" => "staff.subordinate@logbook.com",
            "npp" => "199204142012012004",
            "role" => "Staff",
            "manager_id" => $staffLead->id,
        ]);

        /** @var Collection<int, User> $staffs */
        $staffs = collect([$staffLead, $staffSubordinate]);

        $this->command?->info(
            "Seeded users: exactly 4 (SuperAdmin, Admin, 2 Staff hierarchy)",
        );

        $kpis = collect([
            KpiMaster::factory()->create([
                "nama" => "Meninjau Rencana Kerja Harian",
                "target_angka" => 3,
                "satuan" => "dokumen",
                "deskripsi" =>
                    "Memastikan rencana kerja tim harian tervalidasi dengan baik.",
            ]),
            KpiMaster::factory()->create([
                "nama" => "Koordinasi Tindak Lanjut Lapangan",
                "target_angka" => 6,
                "satuan" => "tiket",
                "deskripsi" =>
                    "Koordinasi penyelesaian tindak lanjut dari temuan lapangan.",
            ]),
            KpiMaster::factory()->create([
                "nama" => "Penyelesaian Laporan Supervisi",
                "target_angka" => 2,
                "satuan" => "laporan",
                "deskripsi" =>
                    "Penyusunan dan penyerahan laporan supervisi mingguan.",
            ]),
            KpiMaster::factory()->create([
                "nama" => "Eksekusi Pemeriksaan Lapangan",
                "target_angka" => 8,
                "satuan" => "unit",
                "deskripsi" =>
                    "Pemeriksaan dan penyelesaian pekerjaan lapangan sesuai target.",
            ]),
            KpiMaster::factory()->create([
                "nama" => "Dokumentasi Bukti Pekerjaan",
                "target_angka" => 10,
                "satuan" => "dokumen",
                "deskripsi" =>
                    "Pengumpulan bukti pekerjaan harian dan dokumentasi pendukung.",
            ]),
            KpiMaster::factory()->create([
                "nama" => "Respon Keluhan Pelanggan",
                "target_angka" => 5,
                "satuan" => "tiket",
                "deskripsi" =>
                    "Penanganan keluhan pelanggan sampai status tindak lanjut jelas.",
            ]),
        ]);

        $staffKpiMap = [
            $staffLead->id => $kpis->take(3)->values(),
            $staffSubordinate->id => $kpis->slice(3, 3)->values(),
        ];

        foreach ($staffs as $staff) {
            $assignedKpis = $staffKpiMap[$staff->id];

            foreach ($assignedKpis as $kpi) {
                UserKpiAssignment::factory()->create([
                    "user_id" => $staff->id,
                    "kpi_id" => $kpi->id,
                    "assigned_by" => $staff->is($staffLead)
                        ? $admin->id
                        : $staffLead->id,
                ]);
            }

            $startDate = Carbon::now()->subDays(14);
            $endDate = Carbon::now()->subDays(1);
            $reviewerId = $staff->is($staffLead) ? $admin->id : $staffLead->id;

            for (
                $date = clone $startDate;
                $date->lte($endDate);
                $date->addDay()
            ) {
                if (
                    $date->isWeekend() &&
                    $staff->is($staffSubordinate) &&
                    rand(0, 1) === 0
                ) {
                    continue;
                }

                $statusChoice = $staff->is($staffLead)
                    ? rand(1, 10)
                    : rand(1, 12);
                $startHour = $staff->is($staffLead) ? 7 : 8;
                $endHour = $staff->is($staffLead) ? 16 : 17;
                $startTime = sprintf(
                    "%02d:%02d:00",
                    rand($startHour, $startHour + 1),
                    rand(0, 59),
                );
                $endTime = sprintf(
                    "%02d:%02d:00",
                    rand($endHour, $endHour + 1),
                    rand(0, 59),
                );
                $lokasiText = $staff->is($staffLead)
                    ? "Kantor Operasional Pusat"
                    : "Area Lapangan Sektor " . (string) rand(1, 4);
                $koordinat = $staff->is($staffLead)
                    ? ["lat" => -6.21, "lng" => 106.84513]
                    : [
                        "lat" => -6.24 + rand(0, 30) / 1000,
                        "lng" => 106.82 + rand(0, 30) / 1000,
                    ];

                if ($statusChoice <= 6) {
                    $logbook = Logbook::factory()
                        ->accepted()
                        ->create([
                            "user_id" => $staff->id,
                            "tanggal" => $date->toDateString(),
                            "start_kerja" => $startTime,
                            "end_kerja" => $endTime,
                            "lokasi" => $lokasiText,
                            "lokasi_lat" => $koordinat["lat"],
                            "lokasi_lng" => $koordinat["lng"],
                            "reviewed_by" => $reviewerId,
                        ]);
                } elseif ($statusChoice <= 8) {
                    $logbook = Logbook::factory()
                        ->submitted()
                        ->create([
                            "user_id" => $staff->id,
                            "tanggal" => $date->toDateString(),
                            "start_kerja" => $startTime,
                            "end_kerja" => $endTime,
                            "lokasi" => $lokasiText,
                            "lokasi_lat" => $koordinat["lat"],
                            "lokasi_lng" => $koordinat["lng"],
                        ]);
                } elseif ($statusChoice <= 10) {
                    $logbook = Logbook::factory()
                        ->rejected()
                        ->create([
                            "user_id" => $staff->id,
                            "tanggal" => $date->toDateString(),
                            "start_kerja" => $startTime,
                            "end_kerja" => $endTime,
                            "lokasi" => $lokasiText,
                            "lokasi_lat" => $koordinat["lat"],
                            "lokasi_lng" => $koordinat["lng"],
                            "reviewed_by" => $reviewerId,
                        ]);
                } else {
                    continue;
                }

                $finishedAt =
                    $logbook->status === "REJECTED"
                        ? null
                        : $date->copy()->setTime(rand(14, 17), rand(0, 59));

                foreach ($assignedKpis as $index => $kpi) {
                    $progressRatio = match ($logbook->status) {
                        "ACCEPTED" => 0.85 + $index * 0.05,
                        "REJECTED" => 0.3 + $index * 0.05,
                        default => 0.55 + $index * 0.04,
                    };
                    $capaianAngka = round(
                        min(
                            $kpi->target_angka * $progressRatio,
                            $kpi->target_angka,
                        ),
                        2,
                    );

                    LogbookKpiDetail::factory()->create([
                        "logbook_id" => $logbook->id,
                        "kpi_id" => $kpi->id,
                        "kpi_nama" => $kpi->nama,
                        "target_angka" => $kpi->target_angka,
                        "satuan" => $kpi->satuan,
                        "lampiran_file" =>
                            $index === 0
                                ? "logbook-kpi-attachments/sample-proof-" .
                                    $staff->id .
                                    "-" .
                                    $date->format("Ymd") .
                                    ".pdf"
                                : null,
                        "capaian_angka" => $capaianAngka,
                        "finished_at" => $finishedAt,
                    ]);
                }
            }

            $todayFinishedAt = Carbon::today()->setTime(
                rand(14, 17),
                rand(0, 59),
            );

            $todayLogbook = Logbook::factory()
                ->submitted()
                ->create([
                    "user_id" => $staff->id,
                    "tanggal" => Carbon::today()->toDateString(),
                    "start_kerja" => $staff->is($staffLead)
                        ? "07:45:00"
                        : "08:20:00",
                    "end_kerja" => $staff->is($staffLead)
                        ? "16:30:00"
                        : "17:10:00",
                    "lokasi" => $staff->is($staffLead)
                        ? "Kantor Operasional Pusat"
                        : "Area Lapangan Sektor 2",
                    "lokasi_lat" => $staff->is($staffLead) ? -6.21 : -6.2235,
                    "lokasi_lng" => $staff->is($staffLead)
                        ? 106.84513
                        : 106.8332,
                ]);

            foreach ($assignedKpis as $index => $kpi) {
                LogbookKpiDetail::factory()->create([
                    "logbook_id" => $todayLogbook->id,
                    "kpi_id" => $kpi->id,
                    "kpi_nama" => $kpi->nama,
                    "target_angka" => $kpi->target_angka,
                    "satuan" => $kpi->satuan,
                    "lampiran_file" =>
                        $index === 0
                            ? "logbook-kpi-attachments/today-proof-" .
                                $staff->id .
                                ".pdf"
                            : null,
                    "capaian_angka" => round(
                        $kpi->target_angka * (0.65 + $index * 0.08),
                        2,
                    ),
                    "finished_at" => $todayFinishedAt,
                ]);
            }
        }
    }
}
