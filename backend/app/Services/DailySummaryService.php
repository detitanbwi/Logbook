<?php

namespace App\Services;

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function syncForLogbook(Logbook $logbook): void
    {
        if (! $logbook->tanggal) {
            return;
        }

        $tanggal = Carbon::parse($logbook->tanggal)->toDateString();

        $this->rebuildStaffSummary($logbook->user_id, $tanggal);
        $this->rebuildKpiSummaries($logbook->user_id, $tanggal);
    }

    public function syncForDetail(LogbookKpiDetail $detail): void
    {
        $logbook = $detail->logbook()->withTrashed()->first();

        if (! $logbook || ! $logbook->tanggal) {
            return;
        }

        $tanggal = Carbon::parse($logbook->tanggal)->toDateString();

        $this->rebuildStaffSummary($logbook->user_id, $tanggal);
        $this->rebuildKpiSummaries($logbook->user_id, $tanggal);
    }

    public function rebuildStaffSummary(string $userId, string $tanggal): void
    {
        $logbooks = Logbook::query()
            ->with('kpiDetails')
            ->where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->get();

        if ($logbooks->isEmpty()) {
            DailyStaffSummary::query()
                ->where('user_id', $userId)
                ->whereDate('tanggal', $tanggal)
                ->delete();

            return;
        }

        $totalTarget = (float) $logbooks->sum(function (Logbook $logbook): float {
            return (float) $logbook->kpiDetails->sum('target_angka');
        });

        $totalCapaian = (float) $logbooks->sum(function (Logbook $logbook): float {
            return (float) $logbook->kpiDetails->sum('capaian_angka');
        });

        $progressPercent = $totalTarget > 0
            ? round(($totalCapaian / $totalTarget) * 100, 2)
            : 0;

        DB::transaction(function () use ($userId, $tanggal, $logbooks, $totalTarget, $totalCapaian, $progressPercent): void {
            DailyStaffSummary::query()
                ->where('user_id', $userId)
                ->whereDate('tanggal', $tanggal)
                ->delete();

            DailyStaffSummary::query()->create([
                'user_id' => $userId,
                'tanggal' => $tanggal,
                'total_logbooks' => $logbooks->count(),
                'submitted_logbooks' => $logbooks->where('status', 'SUBMITTED')->count(),
                'accepted_logbooks' => $logbooks->where('status', 'ACCEPTED')->count(),
                'rejected_logbooks' => $logbooks->where('status', 'REJECTED')->count(),
                'total_work_minutes' => $logbooks->sum(function (Logbook $logbook): int {
                    return $this->calculateNetWorkMinutes((string) $logbook->start_kerja, $logbook->end_kerja);
                }),
                'total_kpi' => $logbooks->sum(function (Logbook $logbook): int {
                    return $logbook->kpiDetails->count();
                }),
                'target_angka_total' => $totalTarget,
                'capaian_angka_total' => $totalCapaian,
                'progress_percent' => $progressPercent,
            ]);
        });
    }

    public function rebuildKpiSummaries(string $userId, string $tanggal): void
    {
        $rows = LogbookKpiDetail::query()
            ->join('logbooks', 'logbooks.id', '=', 'logbook_kpi_details.logbook_id')
            ->where('logbooks.user_id', $userId)
            ->whereDate('logbooks.tanggal', $tanggal)
            ->whereNull('logbooks.deleted_at')
            ->whereNull('logbook_kpi_details.deleted_at')
            ->get([
                'logbook_kpi_details.kpi_id',
                'logbook_kpi_details.kpi_nama',
                'logbook_kpi_details.satuan',
                'logbook_kpi_details.target_angka',
                'logbook_kpi_details.capaian_angka',
                'logbook_kpi_details.lampiran_file',
            ]);

        if ($rows->isEmpty()) {
            DailyKpiSummary::query()
                ->where('user_id', $userId)
                ->whereDate('tanggal', $tanggal)
                ->delete();

            return;
        }

        $groups = $rows->groupBy('kpi_id');
        $keptKpiIds = [];

        /** @var Collection<int, mixed> $group */
        foreach ($groups as $kpiId => $group) {
            $targetTotal = (float) $group->sum('target_angka');
            $capaianTotal = (float) $group->sum('capaian_angka');
            $progressPercent = $targetTotal > 0 ? round(($capaianTotal / $targetTotal) * 100, 2) : 0;

            DB::transaction(function () use ($userId, $kpiId, $tanggal, $group, $targetTotal, $capaianTotal, $progressPercent): void {
                DailyKpiSummary::query()
                    ->where('user_id', $userId)
                    ->where('kpi_id', $kpiId)
                    ->whereDate('tanggal', $tanggal)
                    ->delete();

                DailyKpiSummary::query()->create([
                    'user_id' => $userId,
                    'kpi_id' => $kpiId,
                    'tanggal' => $tanggal,
                    'kpi_nama' => (string) $group->first()->kpi_nama,
                    'satuan' => $group->first()->satuan,
                    'target_angka_total' => $targetTotal,
                    'capaian_angka_total' => $capaianTotal,
                    'progress_percent' => $progressPercent,
                    'total_lampiran' => $group->filter(function ($item): bool {
                        return ! empty($item->lampiran_file);
                    })->count(),
                ]);
            });

            $keptKpiIds[] = $kpiId;
        }

        DailyKpiSummary::query()
            ->where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->whereNotIn('kpi_id', $keptKpiIds)
            ->delete();
    }

    private function calculateNetWorkMinutes(string $startKerja, ?string $endKerja): int
    {
        if (! $endKerja) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i:s', $this->normalizeTime($startKerja));
        $end = Carbon::createFromFormat('H:i:s', $this->normalizeTime($endKerja));

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $grossMinutes = $start->diffInMinutes($end);

        $breakStart = Carbon::createFromFormat('H:i:s', '12:00:00');
        $breakEnd = Carbon::createFromFormat('H:i:s', '13:00:00');

        $overlapStart = $start->greaterThan($breakStart) ? $start : $breakStart;
        $overlapEnd = $end->lessThan($breakEnd) ? $end : $breakEnd;

        $breakMinutes = $overlapEnd->greaterThan($overlapStart)
            ? $overlapStart->diffInMinutes($overlapEnd)
            : 0;

        return max($grossMinutes - $breakMinutes, 0);
    }

    private function normalizeTime(string $value): string
    {
        if (strlen($value) === 5) {
            return $value.':00';
        }

        return $value;
    }
}
