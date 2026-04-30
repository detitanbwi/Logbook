<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\LogbookReview;
use App\Models\LogbookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\OneSignalService;
use Exception;

class LogbookReviewController extends Controller
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    public function index(Request $request)
    {
        // Allow access if user is NOT staff OR if they have subordinates to review
        if (auth()->user()->role === 'staff' && !auth()->user()->subordinates()->exists()) {
            abort(403);
        }

        $user = auth()->user();
        $search = $request->query('search');
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        // Supervisors see logbooks from their subordinates
        $query = Logbook::with(['employee', 'items.kpi', 'latestReview'])
            ->where('supervisor_id', $user->id);

        // Super admins can see everything
        if ($user->role === 'super_admin') {
            $query = Logbook::with(['employee', 'items.kpi', 'latestReview', 'supervisor']);
        }

        // Apply Search (Nama)
        if ($search) {
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        // Apply Sorting
        if ($sort === 'nama') {
            $query->join('users', 'logbooks.employee_id', '=', 'users.id')
                ->select('logbooks.*')
                ->orderBy('users.nama', $direction);
        } elseif ($sort === 'terakhir_diubah') {
            $query->leftJoin('logbook_reviews', function($join) {
                $join->on('logbooks.id', '=', 'logbook_reviews.logbook_id')
                    ->whereRaw('logbook_reviews.id = (SELECT MAX(id) FROM logbook_reviews WHERE logbook_id = logbooks.id)');
            })
            ->select('logbooks.*')
            ->orderBy('logbook_reviews.reviewed_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $logbooks = $query->paginate(20)->withQueryString();

        return view('reviews.index', [
            'logbooks' => $logbooks,
            'title' => 'Review Logbook',
            'active' => 'reviews'
        ]);
    }

    public function edit(Logbook $logbook)
    {
        $this->authorizeReview($logbook);
        $logbook->load(['employee', 'items.kpi', 'attachments', 'latestReview']);

        return view('reviews.edit', [
            'logbook' => $logbook,
            'title' => 'Penilaian Logbook',
            'active' => 'reviews'
        ]);
    }

    public function update(Request $request, Logbook $logbook)
    {
        $this->authorizeReview($logbook);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'items' => 'required_if:status,approved|array',
            'items.*.id' => 'required_if:status,approved|exists:logbook_items,id',
            'items.*.score' => 'required_if:status,approved|integer|min:0|max:100',
            'review_comment' => 'nullable|string',
            'final_score' => 'nullable|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $logbook) {
            $status = $validated['status'];

            if ($status === 'approved') {
                foreach ($validated['items'] as $itemData) {
                    LogbookItem::where('id', $itemData['id'])
                        ->where('logbook_id', $logbook->id)
                        ->update(['score' => $itemData['score']]);
                }
            }

            $finalScore = (int) ($validated['final_score'] ?? 0);
            $rating = max(1, min(5, (int) ceil($finalScore / 20)));

            LogbookReview::create([
                'logbook_id' => $logbook->id,
                'reviewer_id' => auth()->id(),
                'rating' => $rating,
                'comment' => $validated['review_comment'] ?? null,
                'reviewed_at' => now(),
            ]);

            $logbook->update(['status' => $status]);
        });

        // Trigger Notification to Staff
        try {
            $statusLabel = $validated['status'] === 'approved' ? 'DISETUJUI' : 'DITOLAK';
            $this->oneSignal->sendToUser(
                $logbook->employee_id,
                'Review Logbook',
                'Logbook Anda telah ' . $statusLabel . ' oleh ' . auth()->user()->nama . '.',
                ['logbook_id' => $logbook->id]
            );
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send review notification: ' . $e->getMessage());
        }

        return redirect()->route('reviews.index')->with('success', 'Penilaian logbook berhasil diproses.');
    }

    private function authorizeReview(Logbook $logbook)
    {
        if (auth()->user()->role === 'super_admin') return;

        // Cast to int to prevent type mismatch (DB int vs session value)
        if ((int) $logbook->supervisor_id !== (int) auth()->id()) abort(403);
    }
}
