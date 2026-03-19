<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @group Analytics
 */
class AnalyticsController extends Controller
{
    public function adminDashboard(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            abort(403, 'Unauthorized.');
        }

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();

        $data = [
            'total_active_users' => User::query()->whereNull('deleted_at')->count(),
            'total_logbooks_this_month' => Logbook::query()->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)->count(),
            'total_logbooks_last_month' => Logbook::query()->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)->count(),
            'pending_logbooks_count' => Logbook::query()->where('status', 'SUBMITTED')->count(),
            'active_kpis' => KpiMaster::query()->where('status_aktif', true)->count(),
            'logbooks_by_day' => Logbook::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startOfMonth->toDateString())
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]),
            'logbooks_by_status' => Logbook::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => [
                    'status' => $item->status,
                    'count' => (int) $item->count,
                ]),
            'users_by_role' => User::query()
                ->selectRaw('role, COUNT(*) as count')
                ->whereNull('deleted_at')
                ->groupBy('role')
                ->get()
                ->map(fn ($item) => [
                    'role' => $item->role,
                    'count' => (int) $item->count,
                ]),
        ];

        $data['logbook_trend'] = $data['total_logbooks_last_month'] > 0
            ? round((($data['total_logbooks_this_month'] - $data['total_logbooks_last_month']) / $data['total_logbooks_last_month']) * 100, 1)
            : ($data['total_logbooks_this_month'] > 0 ? 100 : 0);

        return response()->json(['data' => $data]);
    }

    public function managerDashboard(Request $request): JsonResponse
    {
        /** @var User $manager */
        $manager = $request->user();

        if (! $manager->hasSubordinates()) {
            abort(403, 'Unauthorized.');
        }

        $now = Carbon::now();
        $subordinates = User::query()->where('manager_id', $manager->id)->get();
        $subordinateStats = [];

        foreach ($subordinates as $subordinate) {
            $logbookIds = Logbook::query()
                ->where('user_id', $subordinate->id)
                ->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year)
                ->pluck('id');

            $targetTotal = (float) LogbookKpiDetail::query()->whereIn('logbook_id', $logbookIds)->sum('target_angka');
            $capaianTotal = (float) LogbookKpiDetail::query()->whereIn('logbook_id', $logbookIds)->sum('capaian_angka');
            $completionRate = $targetTotal > 0 ? round(($capaianTotal / $targetTotal) * 100, 2) : 0;

            $subordinateStats[] = [
                'id' => $subordinate->id,
                'nama' => $subordinate->nama,
                'target_angka_total' => $targetTotal,
                'capaian_angka_total' => $capaianTotal,
                'completion_rate' => $completionRate,
            ];
        }

        $pendingLogbooksCount = Logbook::query()
            ->whereIn('user_id', $subordinates->pluck('id'))
            ->where('status', 'SUBMITTED')
            ->count();

        return response()->json([
            'data' => [
                'subordinates' => $subordinateStats,
                'pending_logbooks_count' => $pendingLogbooksCount,
            ],
        ]);
    }

    public function staffDashboard(Request $request): JsonResponse
    {
        /** @var User $staff */
        $staff = $request->user();

        if (! $staff->isStaff() || $staff->hasSubordinates()) {
            abort(403, 'Unauthorized.');
        }

        $now = Carbon::now();

        $logbookIds = Logbook::query()
            ->where('user_id', $staff->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->pluck('id');

        $targetTotal = (float) LogbookKpiDetail::query()->whereIn('logbook_id', $logbookIds)->sum('target_angka');
        $capaianTotal = (float) LogbookKpiDetail::query()->whereIn('logbook_id', $logbookIds)->sum('capaian_angka');
        $personalKpiCompletionRate = $targetTotal > 0 ? round(($capaianTotal / $targetTotal) * 100, 2) : 0;

        $averageRating = Logbook::query()
            ->where('user_id', $staff->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->whereNotNull('rating')
            ->avg('rating');

        $daysInMonth = $now->daysInMonth;
        $workdaysPassed = 0;
        $currentDay = $now->copy()->startOfMonth();
        $today = min($now->day, $daysInMonth);

        for ($i = 1; $i <= $today; $i++) {
            if (! $currentDay->isWeekend()) {
                $workdaysPassed++;
            }
            $currentDay->addDay();
        }

        $logbooksCount = Logbook::query()
            ->where('user_id', $staff->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->count();

        $missedLogbooksCount = max(0, $workdaysPassed - $logbooksCount);

        $kpiAchievements = LogbookKpiDetail::query()
            ->select('kpi_masters.nama as nama_kpi', DB::raw('COUNT(logbook_kpi_details.id) as total'), DB::raw('SUM(CASE WHEN logbook_kpi_details.capaian_angka >= logbook_kpi_details.target_angka AND logbook_kpi_details.target_angka > 0 THEN 1 ELSE 0 END) as completed'))
            ->join('kpi_masters', 'kpi_masters.id', '=', 'logbook_kpi_details.kpi_id')
            ->whereIn('logbook_id', $logbookIds)
            ->groupBy('kpi_masters.nama')
            ->get()
            ->map(fn ($kpi) => [
                'nama_kpi' => $kpi->nama_kpi,
                'total' => (int) $kpi->total,
                'completed' => (int) $kpi->completed,
                'completion_rate' => (int) $kpi->total > 0 ? round(((int) $kpi->completed / (int) $kpi->total) * 100, 2) : 0,
            ]);

        return response()->json([
            'data' => [
                'personal_kpi_completion_rate' => $personalKpiCompletionRate,
                'total_logbooks' => $logbooksCount,
                'missed_logbooks_count' => $missedLogbooksCount,
                'average_rating' => round((float) $averageRating, 2),
                'kpi_achievements' => $kpiAchievements,
            ],
        ]);
    }

    public function userKpiAchievements(Request $request, User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if (! $currentUser->isPrivileged() && $currentUser->id !== $user->manager_id) {
            abort(403, 'Unauthorized.');
        }

        $now = Carbon::now();
        $logbookIds = Logbook::query()
            ->where('user_id', $user->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->pluck('id');

        $totalKpi = LogbookKpiDetail::query()->whereIn('logbook_id', $logbookIds)->count();
        $completedKpi = LogbookKpiDetail::query()
            ->whereIn('logbook_id', $logbookIds)
            ->whereColumn('capaian_angka', '>=', 'target_angka')
            ->where('target_angka', '>', 0)
            ->count();
        $completionRate = $totalKpi > 0 ? round(($completedKpi / $totalKpi) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'user_nama' => $user->nama,
                'total_kpi_details' => $totalKpi,
                'completed_kpi_details' => $completedKpi,
                'completion_rate' => $completionRate,
            ],
        ]);
    }

    public function exportReports(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isStaff() && ! $user->hasSubordinates()) {
            abort(403, 'Unauthorized.');
        }

        return response()->json([
            'message' => 'Export initiated',
            'download_url' => url('/api/v1/exports/report-'.time().'.pdf'),
        ]);
    }

    public function teamLocations(Request $request): JsonResponse
    {
        /** @var User $manager */
        $manager = $request->user();

        if (! $manager->hasSubordinates() && ! $manager->isPrivileged()) {
            abort(403, 'Unauthorized.');
        }

        $subordinateIds = User::query()->where('manager_id', $manager->id)->pluck('id');

        $locations = Logbook::query()
            ->whereDate('created_at', Carbon::today())
            ->whereIn('user_id', $subordinateIds)
            ->whereNotNull('lokasi')
            ->with('user:id,nama')
            ->get(['id', 'user_id', 'lokasi', 'status', 'created_at'])
            ->map(function ($log) {
                $coords = explode(',', (string) $log->lokasi);

                return [
                    'lat' => (float) trim($coords[0] ?? '0'),
                    'lng' => (float) trim($coords[1] ?? '0'),
                    'title' => $log->user->nama ?? 'Unknown',
                    'status' => $log->status,
                ];
            });

        return response()->json(['data' => $locations]);
    }
}
