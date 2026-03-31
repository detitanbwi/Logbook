<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            // Get date filter from request, default to all data
            try {
                $filterDate = $request->filled('date')
                    ? \Carbon\Carbon::parse($request->get('date'))
                    : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            // Get logbooks (all by default, specific date when filter is set)
            $logbooksQuery = $user->logbooks()
                ->with(['items.kpi', 'latestReview'])
                ->latest('start_time');

            if ($filterDate) {
                $logbooksQuery->whereDate('start_time', $filterDate->toDateString());
            }

            $logbooks = $logbooksQuery->get();

            // Calculate total hours for approved logbooks in the current month
            $totalHours = $user->logbooks()
                ->where('status', 'approved')
                ->whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year)
                ->get()
                ->sum('total_hours');

            return view('dashboard.staff', [
                'logbooks' => $logbooks,
                'totalHours' => $totalHours,
                'filterDate' => $filterDate,
                'isDateFiltered' => (bool) $filterDate,
                'title' => 'Dashboard Saya',
                'active' => 'dashboard'
            ]);
        }

        // Admin/Super Admin Dashboard
        return view('dashboard.index', [
            'totalEmployees' => \App\Models\User::where('role', 'staff')->count(),
            'totalAdmins' => \App\Models\User::where('role', 'admin')->count(),
            'totalKpis' => \App\Models\Kpi::count(),
            'pendingReviews' => \App\Models\Logbook::where('status', 'pending')->count(),
            'title' => 'Dashboard Overview',
            'active' => 'dashboard'
        ]);
    }
}
