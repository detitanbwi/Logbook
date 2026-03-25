<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReviewLogbookRequest;
use App\Http\Resources\V1\LogbookResource;
use App\Models\Logbook;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Manager Logbook
 */
class ManagerLogbookController extends Controller
{
    public function review(ReviewLogbookRequest $request, Logbook $logbook)
    {
        /** @var User $manager */
        $manager = Auth::user();

        $logbookOwner = $logbook->user;
        if (! $logbookOwner) {
            abort(404, 'Logbook owner not found');
        }

        if (! $manager->isPrivileged()) {
            if (! $manager->hasSubordinates()) {
                abort(403, 'You do not have permission to review logbooks');
            }

            if ($logbookOwner->manager_id !== $manager->id) {
                abort(403, 'You can only review your direct subordinates\' logbooks');
            }
        }

        if ($logbook->status !== 'SUBMITTED') {
            return response()->json(['message' => 'Only SUBMITTED logbooks can be reviewed.'], 400);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($logbook, $validated, $manager) {
            $locked = Logbook::query()->lockForUpdate()->find($logbook->id);

            if (! $locked || $locked->status !== 'SUBMITTED') {
                return response()->json(['message' => 'Logbook sudah direview atau tidak tersedia.'], 409);
            }

            $locked->update([
                'status' => $validated['decision'],
                'rating' => $validated['rating'],
                'reviewer_comment' => $validated['reviewer_comment'],
                'reviewed_by' => $manager->id,
                'reviewed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $locked->user_id,
                'title' => $validated['decision'] === 'ACCEPTED' ? 'Logbook Accepted' : 'Logbook Rejected',
                'message' => $validated['decision'] === 'ACCEPTED'
                    ? "Logbook Anda telah diterima dengan rating {$validated['rating']}/5."
                    : "Logbook Anda ditolak dengan rating {$validated['rating']}/5.",
                'type' => $validated['decision'] === 'ACCEPTED' ? 'LOGBOOK_ACCEPTED' : 'LOGBOOK_REJECTED',
                'reference_id' => $locked->id,
                'is_read' => false,
            ]);

            return response()->json([
                'message' => 'Logbook review berhasil disimpan',
                'data' => new LogbookResource($locked->fresh(['user', 'reviewer', 'kpiDetails.kpi'])),
            ]);
        });
    }
}
