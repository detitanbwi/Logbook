<?php

namespace App\Modules\Logbook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogbookController extends Controller
{
    protected $service;

    public function __construct(LogbookService $service)
    {
        $this->service = $service;
    }

    /**
     * Staff: Submit Logbook
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'main_photo' => 'required|image|max:5120', // 5MB limit
            'daily_report' => 'required|string',
            'kpi_items' => 'required|array|min:1',
            'kpi_items.*.kpi_id' => 'required|exists:kpis,id',
            'kpi_items.*.work_description' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB limit
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $logbook = $this->service->storeLogbook($request->user(), $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Logbook berhasil disimpan',
                'data' => $logbook
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Staff: Get My Logbooks
     */
    public function myLogbooks(Request $request)
    {
        $logbooks = $request->user()->logbooks()
            ->with(['items.kpi', 'latestReview'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $logbooks
        ]);
    }

    /**
     * Supervisor: Get Pending Logbooks
     */
    public function pendingLogbooks(Request $request)
    {
        $logbooks = $request->user()->subordinateLogbooks()
            ->where('status', 'pending')
            ->with(['employee', 'items.kpi'])
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $logbooks
        ]);
    }

    /**
     * Show Detail
     */
    public function show($id, Request $request)
    {
        $logbook = \App\Models\Logbook::with(['employee', 'supervisor', 'items.kpi', 'reviews.reviewer', 'attachments'])
            ->findOrFail($id);

        // Authorization check
        if ($request->user()->id !== $logbook->employee_id && $request->user()->id !== $logbook->supervisor_id && $request->user()->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $logbook
        ]);
    }

    /**
     * Supervisor: Review Logbook
     */
    public function review($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kpi_scores' => 'required|array',
            'kpi_scores.*.logbook_item_id' => 'required|exists:logbook_items,id',
            'kpi_scores.*.score' => 'required|integer|min:0',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $logbook = $this->service->reviewLogbook($request->user(), $id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Logbook berhasil disetujui',
                'data' => $logbook
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Supervisor: Reject Logbook
     */
    public function reject($id, Request $request)
    {
        try {
            $logbook = $this->service->rejectLogbook($request->user(), $id, $request->comment);
            return response()->json([
                'success' => true,
                'message' => 'Logbook berhasil ditolak',
                'data' => $logbook
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
