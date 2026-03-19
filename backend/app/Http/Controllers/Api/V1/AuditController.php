<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * @group System & Notifications
 *
 * APIs for managing system-wide audit logs
 */
class AuditController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all audit logs.
     *
     * Menampilkan riwayat aktivitas sistem (untuk Dashboard Admin & Security Auditing)
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "9b1a59f1-331b-4cf7-8b01-e7a9e1e77f0a",
     *       "table_name": "users",
     *       "record_id": "9b1a59f1-331b-4cf7-8b01-e7a9e1e77f01",
     *       "action": "UPDATE",
     *       "old_data": {"role": "STAFF"},
     *       "new_data": {"role": "MANAGER"},
     *       "performed_by": "9b1a59f1-331b-4cf7-8b01-e7a9e1e77f00",
     *       "performed_at": "2024-03-10T10:00:00.000000Z",
     *       "ip_address": "127.0.0.1",
     *       "user_agent": "Mozilla/5.0..."
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 15,
     *     "total": 65
     *   }
     * }
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = AuditLog::with('user:id,name,nip,role');

        // Search by table_name
        if ($search = $request->input('search')) {
            $query->where('table_name', 'LIKE', "%{$search}%");
        }

        // Filter by action
        if ($action = $request->input('action')) {
            $allowedActions = ['created', 'updated', 'deleted'];
            if (in_array($action, $allowedActions)) {
                $query->where('action', $action);
            }
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('performed_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('performed_at', '<=', $dateTo);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'performed_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['performed_at', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->paginate($perPage));
    }
}
