<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\User;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function daily(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = DailyStaffSummary::query()->with('user:id,nama,npp,role,manager_id');

        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            $query->where('user_id', $actor->id);
        } elseif (! $actor->isPrivileged()) {
            $query->whereHas('user', function ($builder) use ($actor): void {
                $builder->where('manager_id', $actor->id);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('tanggal', '<=', $request->input('date_to'));
        }

        return response()->json($query->orderByDesc('tanggal')->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function dailyByUser(Request $request, string $userId)
    {
        /** @var User $actor */
        $actor = $request->user();
        $targetUser = User::query()->findOrFail($userId);

        if (! $actor->canManageUser($targetUser) && $actor->id !== $targetUser->id) {
            abort(403);
        }

        $query = DailyStaffSummary::query()->where('user_id', $targetUser->id);

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('tanggal', '<=', $request->input('date_to'));
        }

        return response()->json($query->orderByDesc('tanggal')->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function period(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = DailyStaffSummary::query();

        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            $query->where('user_id', $actor->id);
        } elseif (! $actor->isPrivileged()) {
            $query->whereIn('user_id', User::query()->where('manager_id', $actor->id)->pluck('id'));
        }

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $data = $query
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('SUM(total_logbooks) as total_logbooks')
            ->selectRaw('SUM(submitted_logbooks) as submitted_logbooks')
            ->selectRaw('SUM(accepted_logbooks) as accepted_logbooks')
            ->selectRaw('SUM(rejected_logbooks) as rejected_logbooks')
            ->selectRaw('SUM(total_work_minutes) as total_work_minutes')
            ->selectRaw('SUM(total_kpi) as total_kpi')
            ->selectRaw('SUM(target_angka_total) as target_angka_total')
            ->selectRaw('SUM(capaian_angka_total) as capaian_angka_total')
            ->first();

        $target = (float) ($data?->target_angka_total ?? 0);
        $capaian = (float) ($data?->capaian_angka_total ?? 0);

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_logbooks' => (int) ($data?->total_logbooks ?? 0),
            'submitted_logbooks' => (int) ($data?->submitted_logbooks ?? 0),
            'accepted_logbooks' => (int) ($data?->accepted_logbooks ?? 0),
            'rejected_logbooks' => (int) ($data?->rejected_logbooks ?? 0),
            'total_work_minutes' => (int) ($data?->total_work_minutes ?? 0),
            'total_kpi' => (int) ($data?->total_kpi ?? 0),
            'target_angka_total' => $target,
            'capaian_angka_total' => $capaian,
            'progress_percent' => $target > 0 ? round(($capaian / $target) * 100, 2) : 0,
        ]);
    }

    public function kpiDaily(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = DailyKpiSummary::query()->with(['user:id,nama,npp,manager_id', 'kpi:id,nama']);

        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            $query->where('user_id', $actor->id);
        } elseif (! $actor->isPrivileged()) {
            $query->whereHas('user', function ($builder) use ($actor): void {
                $builder->where('manager_id', $actor->id);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        return response()->json($query->orderByDesc('tanggal')->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function kpiPeriod(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = DailyKpiSummary::query();

        if ($actor->isStaff()) {
            $query->where('user_id', $actor->id);
        } elseif (! $actor->isPrivileged()) {
            $query->whereIn('user_id', User::query()->where('manager_id', $actor->id)->pluck('id'));
        }

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $data = $query
            ->whereBetween('tanggal', [$dateFrom, $dateTo])
            ->selectRaw('kpi_id, MAX(kpi_nama) as kpi_nama, SUM(target_angka_total) as target_angka_total, SUM(capaian_angka_total) as capaian_angka_total, SUM(total_lampiran) as total_lampiran')
            ->groupBy('kpi_id')
            ->orderBy('kpi_nama')
            ->get()
            ->map(function ($item) {
                $target = (float) $item->target_angka_total;
                $capaian = (float) $item->capaian_angka_total;

                return [
                    'kpi_id' => $item->kpi_id,
                    'kpi_nama' => $item->kpi_nama,
                    'target_angka_total' => $target,
                    'capaian_angka_total' => $capaian,
                    'progress_percent' => $target > 0 ? round(($capaian / $target) * 100, 2) : 0,
                    'total_lampiran' => (int) $item->total_lampiran,
                ];
            });

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'items' => $data,
        ]);
    }

    public function teamDaily(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->hasSubordinates() && ! $actor->isPrivileged()) {
            abort(403);
        }

        $query = DailyStaffSummary::query()
            ->with('user:id,nama,npp,manager_id')
            ->whereIn('user_id', User::query()->where('manager_id', $actor->id)->pluck('id'));

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        return response()->json($query->orderByDesc('tanggal')->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function staffPerformance(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            abort(403);
        }

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $query = DailyStaffSummary::query()
            ->join('users', 'users.id', '=', 'daily_staff_summaries.user_id')
            ->whereBetween('daily_staff_summaries.tanggal', [$dateFrom, $dateTo]);

        if (! $actor->isPrivileged()) {
            $query->where('users.manager_id', $actor->id);
        }

        $items = $query
            ->groupBy('users.id', 'users.nama', 'users.npp')
            ->orderBy('users.nama')
            ->selectRaw('users.id as user_id, users.nama, users.npp')
            ->selectRaw('SUM(daily_staff_summaries.total_logbooks) as total_logbooks')
            ->selectRaw('SUM(daily_staff_summaries.accepted_logbooks) as accepted_logbooks')
            ->selectRaw('SUM(daily_staff_summaries.rejected_logbooks) as rejected_logbooks')
            ->selectRaw('SUM(daily_staff_summaries.target_angka_total) as target_angka_total')
            ->selectRaw('SUM(daily_staff_summaries.capaian_angka_total) as capaian_angka_total')
            ->get()
            ->map(function ($row) {
                $target = (float) $row->target_angka_total;
                $capaian = (float) $row->capaian_angka_total;

                return [
                    'user_id' => $row->user_id,
                    'nama' => $row->nama,
                    'npp' => $row->npp,
                    'total_logbooks' => (int) $row->total_logbooks,
                    'accepted_logbooks' => (int) $row->accepted_logbooks,
                    'rejected_logbooks' => (int) $row->rejected_logbooks,
                    'target_angka_total' => $target,
                    'capaian_angka_total' => $capaian,
                    'progress_percent' => $target > 0 ? round(($capaian / $target) * 100, 2) : 0,
                ];
            });

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'items' => $items,
        ]);
    }
}
