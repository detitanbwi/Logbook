<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Manager Logbook
 */
class ManagerLogbookController extends Controller
{
    public function revert(Request $request, Logbook $logbook)
    {
        $manager = Auth::user();

        if ($manager->role === 'STAFF') {
            abort(403);
        }

        if ($manager->role === 'MANAGER' && $logbook->user->manager_id !== $manager->id) {
            abort(403);
        }

        if ($logbook->status !== 'SUBMITTED') {
            return response()->json(['message' => 'Only SUBMITTED logbooks can be reverted.'], 400);
        }

        $logbook->update([
            'status' => 'DRAFT',
        ]);

        Notification::create([
            'user_id' => $logbook->user_id,
            'title' => 'Logbook Reverted',
            'message' => 'Your logbook has been reverted to DRAFT.',
            'type' => 'LOGBOOK_REVERTED',
            'reference_id' => $logbook->id,
            'is_read' => false,
        ]);

        return response()->json($logbook);
    }

    public function rate(Request $request, Logbook $logbook)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $manager = Auth::user();

        if ($manager->role === 'STAFF') {
            abort(403);
        }

        if ($manager->role === 'MANAGER' && $logbook->user->manager_id !== $manager->id) {
            abort(403);
        }

        if ($logbook->status !== 'SUBMITTED') {
            return response()->json(['message' => 'Only SUBMITTED logbooks can be rated.'], 400);
        }

        $logbook->update([
            'status' => 'REVIEWED',
            'rating' => $request->rating,
            'reviewed_by' => $manager->id,
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $logbook->user_id,
            'title' => 'Logbook Reviewed',
            'message' => "Your logbook has been reviewed and rated {$request->rating}/5.",
            'type' => 'LOGBOOK_REVIEWED',
            'reference_id' => $logbook->id,
            'is_read' => false,
        ]);

        return response()->json($logbook);
    }
}
