<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\KpiAssignmentResource;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group KPI Assignments
 */
class KpiAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = UserKpiAssignment::with(['user', 'kpi', 'assigner']);

        if ($user->role === 'MANAGER') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        } elseif ($user->role === 'STAFF') {
            abort(403, 'Unauthorized action.');
        }

        $perPage = $request->query('per_page', 15);

        return KpiAssignmentResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'kpi_id' => 'required|exists:kpi_masters,id',
        ]);

        $manager = Auth::user();
        if ($manager->role !== 'MANAGER' && $manager->role !== 'ADMIN') {
            abort(403, 'Unauthorized');
        }

        $staff = User::findOrFail($request->user_id);

        if ($manager->role === 'MANAGER' && $staff->manager_id !== $manager->id) {
            return response()->json(['message' => 'User is not your subordinate.'], 403);
        }

        $assignment = UserKpiAssignment::create([
            'user_id' => $staff->id,
            'kpi_id' => $request->kpi_id,
            'assigned_by' => $manager->id,
        ]);

        Notification::create([
            'user_id' => $staff->id,
            'title' => 'KPI Baru Ditugaskan',
            'message' => 'You have been assigned a new KPI.',
            'type' => 'KPI_ASSIGNMENT',
            'reference_id' => $assignment->id,
            'is_read' => false,
        ]);

        return response()->json($assignment, 201);
    }

    public function destroy(UserKpiAssignment $assignment)
    {
        $manager = Auth::user();

        if ($manager->role === 'MANAGER') {
            $staff = $assignment->user;
            if ($staff->manager_id !== $manager->id) {
                abort(403, 'Unauthorized');
            }
        } elseif ($manager->role === 'STAFF') {
            abort(403, 'Unauthorized');
        }

        $assignment->delete();

        return response()->json(null, 204);
    }

    public function me()
    {
        $assignments = UserKpiAssignment::with(['kpi', 'assigner'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($assignments);
    }
}
