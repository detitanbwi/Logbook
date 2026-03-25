<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAssignmentRequest;
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
        /** @var User $user */
        $user = Auth::user();
        $query = UserKpiAssignment::with(['user', 'kpi', 'assigner']);

        if ($user->isStaff() && ! $user->hasSubordinates()) {
            abort(403, 'Unauthorized action.');
        }

        if (! $user->isPrivileged()) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $perPage = $request->query('per_page', 15);

        return KpiAssignmentResource::collection($query->paginate($perPage));
    }

    public function store(StoreAssignmentRequest $request)
    {
        $validated = $request->validated();

        /** @var User $actor */
        $actor = Auth::user();
        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            abort(403, 'Unauthorized');
        }

        $staff = User::findOrFail($validated['user_id']);

        if (! $actor->isPrivileged() && $staff->manager_id !== $actor->id) {
            return response()->json(['message' => 'User is not your subordinate.'], 403);
        }

        $assignment = UserKpiAssignment::create([
            'user_id' => $staff->id,
            'kpi_id' => $validated['kpi_id'],
            'assigned_by' => $actor->id,
        ]);

        Notification::create([
            'user_id' => $staff->id,
            'title' => 'KPI Baru Ditugaskan',
            'message' => 'You have been assigned a new KPI.',
            'type' => 'KPI_ASSIGNMENT',
            'reference_id' => null,
            'is_read' => false,
        ]);

        return (new KpiAssignmentResource($assignment->load(['user', 'kpi', 'assigner'])))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(UserKpiAssignment $assignment)
    {
        /** @var User $actor */
        $actor = Auth::user();

        if ($actor->isStaff() && ! $actor->hasSubordinates()) {
            abort(403, 'Unauthorized');
        }

        $staff = $assignment->user;
        if (! $actor->isPrivileged() && $staff->manager_id !== $actor->id) {
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
