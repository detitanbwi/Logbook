<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateExistingDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->canBackfill()) {
            return;
        }

        $kpiByLogbookSubquery = DB::table('logbook_kpi_details as d')
            ->whereNull('d.deleted_at')
            ->groupBy('d.logbook_id')
            ->selectRaw('d.logbook_id')
            ->selectRaw('COUNT(d.id) as total_kpi')
            ->selectRaw('COALESCE(SUM(d.target_angka), 0) as target_angka_total')
            ->selectRaw('COALESCE(SUM(d.capaian_angka), 0) as capaian_angka_total');

        $staffRows = DB::table('logbooks as l')
            ->leftJoin('v_logbook_work_duration as wd', 'wd.logbook_id', '=', 'l.id')
            ->leftJoinSub($kpiByLogbookSubquery, 'kpi_per_logbook', function ($join): void {
                $join->on('kpi_per_logbook.logbook_id', '=', 'l.id');
            })
            ->whereNull('l.deleted_at')
            ->groupBy('l.user_id', 'l.tanggal')
            ->selectRaw('l.user_id, l.tanggal')
            ->selectRaw('COUNT(DISTINCT l.id) as total_logbooks')
            ->selectRaw("COUNT(DISTINCT CASE WHEN l.status = 'SUBMITTED' THEN l.id END) as submitted_logbooks")
            ->selectRaw("COUNT(DISTINCT CASE WHEN l.status = 'ACCEPTED' THEN l.id END) as accepted_logbooks")
            ->selectRaw("COUNT(DISTINCT CASE WHEN l.status = 'REJECTED' THEN l.id END) as rejected_logbooks")
            ->selectRaw('COALESCE(SUM(wd.net_work_minutes), 0) as total_work_minutes')
            ->selectRaw('COALESCE(SUM(kpi_per_logbook.total_kpi), 0) as total_kpi')
            ->selectRaw('COALESCE(SUM(kpi_per_logbook.target_angka_total), 0) as target_angka_total')
            ->selectRaw('COALESCE(SUM(kpi_per_logbook.capaian_angka_total), 0) as capaian_angka_total')
            ->get();

        $now = now();

        $staffUpserts = $staffRows->map(function (object $row) use ($now): array {
            $target = (float) $row->target_angka_total;
            $capaian = (float) $row->capaian_angka_total;

            return [
                'id' => (string) Str::uuid(),
                'user_id' => $row->user_id,
                'tanggal' => $row->tanggal,
                'total_logbooks' => (int) $row->total_logbooks,
                'submitted_logbooks' => (int) $row->submitted_logbooks,
                'accepted_logbooks' => (int) $row->accepted_logbooks,
                'rejected_logbooks' => (int) $row->rejected_logbooks,
                'total_work_minutes' => (int) $row->total_work_minutes,
                'total_kpi' => (int) $row->total_kpi,
                'target_angka_total' => $target,
                'capaian_angka_total' => $capaian,
                'progress_percent' => $target > 0 ? min(round(($capaian / $target) * 100, 2), 999.99) : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        if ($staffUpserts !== []) {
            DB::table('daily_staff_summaries')->upsert(
                $staffUpserts,
                ['user_id', 'tanggal'],
                [
                    'total_logbooks',
                    'submitted_logbooks',
                    'accepted_logbooks',
                    'rejected_logbooks',
                    'total_work_minutes',
                    'total_kpi',
                    'target_angka_total',
                    'capaian_angka_total',
                    'progress_percent',
                    'updated_at',
                ],
            );
        }

        $kpiRows = DB::table('logbooks as l')
            ->join('logbook_kpi_details as d', 'd.logbook_id', '=', 'l.id')
            ->join('kpi_masters as k', 'k.id', '=', 'd.kpi_id')
            ->whereNull('l.deleted_at')
            ->whereNull('d.deleted_at')
            ->groupBy('l.user_id', 'd.kpi_id', 'l.tanggal', 'k.nama', 'd.satuan')
            ->selectRaw('l.user_id, d.kpi_id, l.tanggal, k.nama as kpi_nama, d.satuan')
            ->selectRaw('COALESCE(SUM(d.target_angka), 0) as target_angka_total')
            ->selectRaw('COALESCE(SUM(d.capaian_angka), 0) as capaian_angka_total')
            ->selectRaw("COALESCE(SUM(CASE WHEN d.lampiran_file IS NOT NULL AND d.lampiran_file <> '' THEN 1 ELSE 0 END), 0) as total_lampiran")
            ->get();

        $kpiUpserts = $kpiRows->map(function (object $row) use ($now): array {
            $target = (float) $row->target_angka_total;
            $capaian = (float) $row->capaian_angka_total;

            return [
                'id' => (string) Str::uuid(),
                'user_id' => $row->user_id,
                'kpi_id' => $row->kpi_id,
                'tanggal' => $row->tanggal,
                'kpi_nama' => $row->kpi_nama,
                'satuan' => $row->satuan,
                'target_angka_total' => $target,
                'capaian_angka_total' => $capaian,
                'progress_percent' => $target > 0 ? min(round(($capaian / $target) * 100, 2), 999.99) : 0,
                'total_lampiran' => (int) $row->total_lampiran,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        if ($kpiUpserts !== []) {
            DB::table('daily_kpi_summaries')->upsert(
                $kpiUpserts,
                ['user_id', 'kpi_id', 'tanggal'],
                [
                    'kpi_nama',
                    'satuan',
                    'target_angka_total',
                    'capaian_angka_total',
                    'progress_percent',
                    'total_lampiran',
                    'updated_at',
                ],
            );
        }
    }

    private function canBackfill(): bool
    {
        return DB::getSchemaBuilder()->hasTable('daily_staff_summaries')
            && DB::getSchemaBuilder()->hasTable('daily_kpi_summaries')
            && DB::getSchemaBuilder()->hasTable('logbooks')
            && DB::getSchemaBuilder()->hasTable('logbook_kpi_details')
            && DB::getSchemaBuilder()->hasTable('kpi_masters');
    }
}
