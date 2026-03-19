<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LogbookResource;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\Notification;
use App\Models\UserKpiAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

/**
 * @group Staff Logbook
 */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Logbook::with(['user', 'reviewer']);

        if ($user->role === 'STAFF') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'MANAGER') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('manager_id', $user->id)->orWhere('id', $user->id);
            });
        }

        // Search by user name (join with users table)
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $allowedStatuses = ['DRAFT', 'SUBMITTED', 'REVERTED', 'REVIEWED'];
            if (in_array($status, $allowedStatuses)) {
                $query->where('status', $status);
            }
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('start_kerja', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('start_kerja', '<=', $dateTo);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['created_at', 'start_kerja', 'status'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return LogbookResource::collection($query->paginate($perPage));
    }

    public function show(Logbook $logbook)
    {
        $user = Auth::user();

        if ($user->role === 'STAFF' && $logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'MANAGER' && $logbook->user_id !== $user->id && $logbook->user->manager_id !== $user->id) {
            abort(403);
        }

        $logbook->load(['user', 'reviewer', 'kpiDetails.kpi']);

        return response()->json($logbook);
    }

    public function start(Request $request)
    {
        $request->validate([
            'gps_location_start' => 'required|string',
        ]);

        $user = Auth::user();

        $logbook = Logbook::create([
            'user_id' => $user->id,
            'status' => 'DRAFT',
            'start_kerja' => now(),
            'lokasi_start' => $request->gps_location_start,
        ]);

        $activeAssignments = UserKpiAssignment::with('kpi')->where('user_id', $user->id)->get();

        foreach ($activeAssignments as $assignment) {
            LogbookKpiDetail::create([
                'logbook_id' => $logbook->id,
                'kpi_id' => $assignment->kpi_id,
                'kpi_nama' => $assignment->kpi->nama ?? 'Unknown KPI',
                'is_finished' => false,
            ]);
        }

        $logbook->load('kpiDetails');

        return response()->json($logbook, 201);
    }

    public function toggleKpi(Request $request, Logbook $logbook, LogbookKpiDetail $detail)
    {
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($detail->logbook_id !== $logbook->id) {
            return response()->json(['message' => 'Detail mismatch'], 400);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Can only toggle KPIs on DRAFT logbook'], 400);
        }

        $request->validate([
            'is_finished' => 'required|boolean',
        ]);

        $detail->update([
            'is_finished' => $request->is_finished,
            'finished_at' => $request->is_finished ? now() : null,
        ]);

        return response()->json($detail);
    }

    public function submit(Request $request, Logbook $logbook)
    {
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya logbook DRAFT yang dapat disubmit'], 400);
        }

        $request->validate([
            'gps_location_end' => 'required|string',
            'gambar_bukti' => 'nullable|array',
            'gambar_bukti.*' => 'nullable|image|max:5120', // 5MB max per image
        ]);

        $proofs = [];
        if ($request->has('gambar_bukti')) {
            foreach ($request->gambar_bukti as $bukti) {
                if ($bukti instanceof UploadedFile) {
                    $path = $bukti->store('proofs', 'public');
                    $proofs[] = '/storage/'.$path;
                } elseif (is_string($bukti)) {
                    $proofs[] = $bukti;
                }
            }
        }

        $logbook->update([
            'status' => 'SUBMITTED',
            'end_kerja' => now(),
            'lokasi_end' => $request->gps_location_end,
            'gambar_bukti' => empty($proofs) ? [] : $proofs,
        ]);

        if ($user->manager_id) {
            Notification::create([
                'user_id' => $user->manager_id,
                'title' => 'Logbook Menunggu Review',
                'message' => "{$user->name} telah mensubmit logbook mereka.",
                'type' => 'LOGBOOK_SUBMITTED',
                'reference_id' => $logbook->id,
                'is_read' => false,
            ]);
        }

        return response()->json($logbook);
    }
}
