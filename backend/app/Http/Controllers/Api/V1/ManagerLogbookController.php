<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LogbookResource;
use App\Models\Logbook;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Manager Logbook
 */
class ManagerLogbookController extends Controller
{
    public function review(Request $request, Logbook $logbook)
    {
        /** @var User $manager */
        $manager = Auth::user();

        if ($manager->isStaff() && ! $manager->hasSubordinates()) {
            abort(403);
        }

        if (! $manager->isPrivileged() && $logbook->user?->manager_id !== $manager->id) {
            abort(403);
        }

        if ($logbook->status !== 'SUBMITTED') {
            return response()->json(['message' => 'Only SUBMITTED logbooks can be reviewed.'], 400);
        }

        $validated = $request->validate([
            'decision' => 'required|string|in:ACCEPTED,REJECTED',
            'rating' => 'required|integer|min:1|max:5',
            'reviewer_comment' => 'nullable|string|max:5000',
        ]);

        $logbook->update([
            'status' => $validated['decision'],
            'rating' => $validated['rating'],
            'reviewer_comment' => $validated['reviewer_comment'] ?? null,
            'reviewed_by' => $manager->id,
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $logbook->user_id,
            'title' => $validated['decision'] === 'ACCEPTED' ? 'Logbook Accepted' : 'Logbook Rejected',
            'message' => $validated['decision'] === 'ACCEPTED'
                ? "Logbook Anda telah diterima dengan rating {$validated['rating']}/5."
                : "Logbook Anda ditolak dengan rating {$validated['rating']}/5.",
            'type' => $validated['decision'] === 'ACCEPTED' ? 'LOGBOOK_ACCEPTED' : 'LOGBOOK_REJECTED',
            'reference_id' => $logbook->id,
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Logbook review berhasil disimpan',
            'data' => new LogbookResource($logbook->fresh(['user', 'reviewer', 'kpiDetails.kpi'])),
        ]);
    }

    public function rate(Request $request, Logbook $logbook)
    {
        abort(410, 'Endpoint removed. Use PUT /api/v1/logbooks/{logbook}/review.');
    }

    public function revert(Request $request, Logbook $logbook)
    {
        abort(410, 'Endpoint removed. Use PUT /api/v1/logbooks/{logbook}/review with decision REJECTED.');
    }
}
