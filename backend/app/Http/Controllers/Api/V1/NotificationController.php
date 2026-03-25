<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Notifications
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = Notification::query()->where('user_id', $actor->id);

        // Filter by is_read / unread_only
        $isReadFilter = null;

        if ($request->has('is_read')) {
            $isReadFilter = $request->boolean('is_read');
        }

        if ($request->boolean('unread_only')) {
            $isReadFilter = false;
        }

        if ($isReadFilter !== null) {
            $query->where('is_read', $isReadFilter);
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        $paginated = $query->paginate($perPage);
        $paginated->setCollection($paginated->getCollection()->map(function (Notification $notification): array {
            $payload = $notification->toArray();
            $payload['preview_message'] = $notification->preview_message;
            $payload['target_path'] = $notification->target_path;
            $payload['target_params'] = $notification->target_params;

            return $payload;
        }));

        return response()->json($paginated);
    }

    public function read(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
            ]);
        }

        return response()->json($notification->refresh());
    }

    public function readAll()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
