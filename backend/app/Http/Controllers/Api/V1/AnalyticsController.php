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
        if ($request->user()->role !== 'ADMIN') {
            abort(403, 'Unauthorized.');
        }

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();

        // Base stats
        $data = [
            'total_active_users' => User::whereNull('deleted_at')->count(),
            'total_logbooks_this_month' => Logbook::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)->count(),
            'total_logbooks_last_month' => Logbook::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)->count(),
            'pending_logbooks_count' => Logbook::where('status', 'SUBMITTED')->count(),
            'active_kpis' => KpiMaster::where('status_aktif', true)->count(),

            // Trend data: Logbooks per day for current month
            'logbooks_by_day' => Logbook::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startOfMonth)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => (int) $item->count,
                    ];
                }),

            // Status breakdown
            'logbooks_by_status' => Logbook::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'count' => (int) $item->count,
                    ];
                }),

            // User role breakdown
            'users_by_role' => User::selectRaw('role, COUNT(*) as count')
                ->whereNull('deleted_at')
                ->groupBy('role')
                ->get()
                ->map(function ($item) {
                    return [
                        'role' => $item->role,
                        'count' => (int) $item->count,
                    ];
                }),
        ];

        // Calculate trends percentage
        $data['logbook_trend'] = $data['total_logbooks_last_month'] > 0
            ? round((($data['total_logbooks_this_month'] - $data['total_logbooks_last_month']) / $data['total_logbooks_last_month']) * 100, 1)
            : ($data['total_logbooks_this_month'] > 0 ? 100 : 0);

        return response()->json(['data' => $data]);
    }

    public function managerDashboard(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'MANAGER') {
            abort(403, 'Unauthorized.');
        }

        $manager = $request->user();
        $now = Carbon::now();

        $subordinates = User::where('manager_id', $manager->id)->get();
        $subordinateStats = [];

        foreach ($subordinates as $sub) {
            $logbooks = Logbook::where('user_id', $sub->id)
                ->whereMonth('start_kerja', $now->month)
                ->whereYear('start_kerja', $now->year)
                ->get();

            $logbookIds = $logbooks->pluck('id');

            $totalKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)->count();
            $completedKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)
                ->where('is_finished', true)
                ->count();

            $subordinateStats[] = [
                'id' => $sub->id,
                'name' => $sub->name,
                'total_kpi' => $totalKpi,
                'completed_kpi' => $completedKpi,
                'completion_rate' => $totalKpi > 0 ? round(($completedKpi / $totalKpi) * 100, 2) : 0,
            ];
        }

        $pendingLogbooksCount = Logbook::whereIn('user_id', $subordinates->pluck('id'))
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
        if ($request->user()->role !== 'STAFF') {
            abort(403, 'Unauthorized.');
        }

        $staff = $request->user();
        $now = Carbon::now();

        $logbookIds = Logbook::where('user_id', $staff->id)
            ->whereMonth('start_kerja', $now->month)
            ->whereYear('start_kerja', $now->year)
            ->pluck('id');

        $totalKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)->count();
        $completedKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)
            ->where('is_finished', true)
            ->count();

        $personalKpiCompletionRate = $totalKpi > 0 ? round(($completedKpi / $totalKpi) * 100, 2) : 0;

        $averageRating = Logbook::where('user_id', $staff->id)
            ->whereMonth('start_kerja', $now->month)
            ->whereYear('start_kerja', $now->year)
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

        $logbooksCount = Logbook::where('user_id', $staff->id)
            ->whereMonth('start_kerja', $now->month)
            ->whereYear('start_kerja', $now->year)
            ->count();

        $missedLogbooksCount = max(0, $workdaysPassed - $logbooksCount);

        $kpiAchievements = LogbookKpiDetail::select('kpi_masters.nama as nama_kpi', DB::raw('COUNT(logbook_kpi_details.id) as total'), DB::raw('SUM(CASE WHEN is_finished = true THEN 1 ELSE 0 END) as completed'))
            ->join('kpi_masters', 'kpi_masters.id', '=', 'logbook_kpi_details.kpi_id')
            ->whereIn('logbook_id', $logbookIds)
            ->groupBy('kpi_masters.nama')
            ->get()
            ->map(function ($kpi) {
                return [
                    'name' => $kpi->nama_kpi,
                    'total' => (int) $kpi->total,
                    'completed' => (int) $kpi->completed,
                    'completion_rate' => $kpi->total > 0 ? round(($kpi->completed / $kpi->total) * 100, 2) : 0,
                ];
            });

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
        $currentUser = $request->user();

        if ($currentUser->role !== 'ADMIN' && $currentUser->id !== $user->manager_id) {
            abort(403, 'Unauthorized.');
        }

        $now = Carbon::now();
        $logbookIds = Logbook::where('user_id', $user->id)
            ->whereMonth('start_kerja', $now->month)
            ->whereYear('start_kerja', $now->year)
            ->pluck('id');

        $totalKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)->count();
        $completedKpi = LogbookKpiDetail::whereIn('logbook_id', $logbookIds)
            ->where('is_finished', true)
            ->count();

        $completionRate = $totalKpi > 0 ? round(($completedKpi / $totalKpi) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_kpi_details' => $totalKpi,
                'completed_kpi_details' => $completedKpi,
                'completion_rate' => $completionRate,
            ],
        ]);
    }

    public function exportReports(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['ADMIN', 'MANAGER'])) {
            abort(403, 'Unauthorized.');
        }

        return response()->json([
            'message' => 'Export initiated',
            'download_url' => url('/api/v1/exports/report-'.time().'.pdf'),
        ]);
    }

    /**
     * Get team member locations for the manager dashboard map.
     */
    public function teamLocations(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'MANAGER') {
            abort(403, 'Unauthorized.');
        }

        $subordinateIds = User::where('manager_id', $request->user()->id)->pluck('id');

        $locations = Logbook::query()
            ->whereDate('created_at', Carbon::today())
            ->whereIn('user_id', $subordinateIds)
            ->whereNotNull('lokasi_start')
            ->with('user:id,name')
            ->get(['id', 'user_id', 'lokasi_start', 'status', 'created_at'])
            ->map(function ($log) {
                $coords = explode(',', $log->lokasi_start);

                return [
                    'lat' => (float) trim($coords[0] ?? '0'),
                    'lng' => (float) trim($coords[1] ?? '0'),
                    'title' => $log->user->name ?? 'Unknown',
                    'status' => $log->status,
                ];
            });

        return response()->json(['data' => $locations]);
    }
}
