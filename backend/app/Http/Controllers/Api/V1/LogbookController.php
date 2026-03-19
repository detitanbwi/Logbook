<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LogbookResource;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @group Staff Logbook
 */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Logbook::query()->with(['user', 'reviewer', 'kpiDetails.kpi']);

        if ($user->isStaff()) {
            $query->where('user_id', $user->id);
        } elseif (! $user->isPrivileged()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('user_id', $user->id)
                    ->orWhereHas('user', function ($relation) use ($user): void {
                        $relation->where('manager_id', $user->id);
                    });
            });
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($relation) use ($search): void {
                $relation->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('npp', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['DRAFT', 'SUBMITTED', 'ACCEPTED', 'REJECTED'], true)) {
                $query->where('status', $status);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('tanggal', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('tanggal', '<=', $dateTo);
        }

        $sortBy = $request->input('sort_by', 'tanggal');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['tanggal', 'start_kerja', 'end_kerja', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return LogbookResource::collection($query->paginate($perPage));
    }

    public function show(Logbook $logbook)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $this->canAccessLogbook($user, $logbook)) {
            abort(403);
        }

        $logbook->load(['user', 'reviewer', 'kpiDetails.kpi']);

        return new LogbookResource($logbook);
    }

    public function start(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isStaff()) {
            return response()->json(['message' => 'Hanya Staff yang dapat membuat logbook'], 403);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'start_kerja' => 'required|date_format:H:i',
            'end_kerja' => 'nullable|date_format:H:i',
            'lokasi' => 'required|string|max:1000',
        ]);

        if ($validated['start_kerja'] < '07:00') {
            return response()->json(['message' => 'Jam mulai minimal 07:00'], 422);
        }

        if (($validated['end_kerja'] ?? null) !== null && $validated['end_kerja'] <= $validated['start_kerja']) {
            return response()->json(['message' => 'Jam selesai harus lebih besar dari jam mulai'], 422);
        }

        $logbook = Logbook::create([
            'user_id' => $user->id,
            'tanggal' => $validated['tanggal'],
            'start_kerja' => $validated['start_kerja'],
            'end_kerja' => $validated['end_kerja'] ?? null,
            'lokasi' => $validated['lokasi'],
            'status' => 'DRAFT',
        ]);

        $activeAssignments = UserKpiAssignment::query()
            ->with('kpi')
            ->where('user_id', $user->id)
            ->get();

        $seenKpiIds = [];
        foreach ($activeAssignments as $assignment) {
            // Prevent duplicate KPI details from duplicate assignments
            if (in_array($assignment->kpi_id, $seenKpiIds, true)) {
                continue;
            }
            $seenKpiIds[] = $assignment->kpi_id;

            $kpi = $assignment->kpi;

            LogbookKpiDetail::create([
                'logbook_id' => $logbook->id,
                'kpi_id' => $assignment->kpi_id,
                'kpi_nama' => (string) ($kpi?->nama ?? 'Unknown KPI'),
                'target_angka' => (float) ($kpi?->target_angka ?? 0),
                'satuan' => $kpi?->satuan,
                'capaian_angka' => 0,
            ]);
        }

        $logbook->load(['user', 'reviewer', 'kpiDetails.kpi']);

        return (new LogbookResource($logbook))
            ->response()
            ->setStatusCode(201);
    }

    public function store(Request $request)
    {
        return $this->start($request);
    }

    /**
     * Update logbook time fields (DRAFT status only)
     *
     * Per plan spec section 9.2: PATCH /logbooks/{id}
     * Allows updating tanggal, start_kerja, end_kerja for DRAFT logbooks.
     */
    public function update(Request $request, Logbook $logbook)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya logbook DRAFT yang dapat diupdate'], 400);
        }

        $validated = $request->validate([
            'tanggal' => 'sometimes|date',
            'start_kerja' => 'sometimes|date_format:H:i',
            'end_kerja' => 'sometimes|nullable|date_format:H:i',
            'lokasi' => 'sometimes|string|max:1000',
        ]);

        // Determine actual values for validation
        $startKerja = $validated['start_kerja'] ?? $logbook->start_kerja;
        $endKerja = $validated['end_kerja'] ?? $logbook->end_kerja;

        // Normalize start_kerja for comparison (handle both string and Carbon instances)
        $startKerjaStr = $startKerja instanceof Carbon
            ? $startKerja->format('H:i')
            : (string) $startKerja;

        if (isset($validated['start_kerja']) && $startKerjaStr < '07:00') {
            return response()->json(['message' => 'Jam mulai minimal 07:00'], 422);
        }

        // Validate end_kerja > start_kerja if both are set
        if ($endKerja !== null) {
            $endKerjaStr = $endKerja instanceof Carbon
                ? $endKerja->format('H:i')
                : (string) $endKerja;

            if ($endKerjaStr <= $startKerjaStr) {
                return response()->json(['message' => 'Jam selesai harus lebih besar dari jam mulai'], 422);
            }
        }

        $logbook->update($validated);

        $logbook->load(['user', 'reviewer', 'kpiDetails.kpi']);

        return response()->json([
            'message' => 'Logbook berhasil diperbarui',
            'data' => new LogbookResource($logbook),
        ]);
    }

    public function updateProgress(Request $request, Logbook $logbook, LogbookKpiDetail $detail)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($detail->logbook_id !== $logbook->id) {
            return response()->json(['message' => 'Detail mismatch'], 400);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya logbook DRAFT yang dapat diupdate'], 400);
        }

        $validated = $request->validate([
            'capaian_angka' => 'required|numeric|min:0',
        ]);

        $capaianAngka = (float) $validated['capaian_angka'];

        $detail->update([
            'capaian_angka' => $capaianAngka,
            'finished_at' => $capaianAngka > 0 ? now() : null,
        ]);

        return response()->json([
            'id' => $detail->id,
            'capaian_angka' => (float) $detail->capaian_angka,
            'target_angka' => (float) $detail->target_angka,
            'satuan' => $detail->satuan,
            'finished_at' => optional($detail->finished_at)?->toISOString(),
        ]);
    }

    public function uploadAttachment(Request $request, Logbook $logbook, LogbookKpiDetail $detail)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($detail->logbook_id !== $logbook->id) {
            return response()->json(['message' => 'Detail mismatch'], 400);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Lampiran hanya bisa diubah pada DRAFT'], 400);
        }

        $validated = $request->validate([
            'lampiran_file' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        if ($detail->lampiran_file) {
            Storage::disk('public')->delete($detail->lampiran_file);
        }

        $path = $validated['lampiran_file']->store('logbook-kpi-attachments', 'public');

        $detail->update([
            'lampiran_file' => $path,
        ]);

        return response()->json([
            'message' => 'Lampiran KPI berhasil diunggah',
            'data' => [
                'id' => $detail->id,
                'lampiran_file' => $detail->lampiran_file,
            ],
        ]);
    }

    public function deleteAttachment(Logbook $logbook, LogbookKpiDetail $detail)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($detail->logbook_id !== $logbook->id) {
            return response()->json(['message' => 'Detail mismatch'], 400);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Lampiran hanya bisa diubah pada DRAFT'], 400);
        }

        if ($detail->lampiran_file) {
            Storage::disk('public')->delete($detail->lampiran_file);
        }

        $detail->update([
            'lampiran_file' => null,
        ]);

        return response()->json([
            'message' => 'Lampiran KPI berhasil dihapus',
            'data' => [
                'id' => $detail->id,
                'lampiran_file' => null,
            ],
        ]);
    }

    public function submit(Logbook $logbook)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya logbook DRAFT yang dapat disubmit'], 400);
        }

        if (! $logbook->end_kerja) {
            return response()->json(['message' => 'Jam selesai harus diisi sebelum submit'], 422);
        }

        $hasAnyProgress = $logbook->kpiDetails()->where('capaian_angka', '>', 0)->exists();
        if (! $hasAnyProgress) {
            return response()->json([
                'message' => 'Minimal satu KPI harus memiliki capaian lebih dari 0 sebelum submit',
            ], 422);
        }

        $logbook->update([
            'status' => 'SUBMITTED',
        ]);

        $managerId = $logbook->user?->manager_id;
        if ($managerId) {
            Notification::create([
                'user_id' => $managerId,
                'title' => 'Logbook Submitted',
                'message' => 'Terdapat logbook baru yang menunggu review.',
                'type' => 'LOGBOOK_SUBMITTED',
                'reference_id' => $logbook->id,
                'is_read' => false,
            ]);
        }

        return response()->json((new LogbookResource($logbook->fresh(['user', 'reviewer', 'kpiDetails.kpi'])))->toArray(request()));
    }

    public function duration(Logbook $logbook)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $this->canAccessLogbook($user, $logbook)) {
            abort(403);
        }

        return response()->json([
            'logbook_id' => $logbook->id,
            'tanggal' => optional($logbook->tanggal)?->toDateString(),
            'start_kerja' => (string) $logbook->start_kerja,
            'end_kerja' => $logbook->end_kerja,
            'gross_work_minutes' => $logbook->grossWorkMinutes(),
            'break_overlap_minutes' => $logbook->breakOverlapMinutes(),
            'net_work_minutes' => $logbook->netWorkMinutes(),
        ]);
    }

    /**
     * Delete a DRAFT logbook.
     *
     * Per plan spec section 9.3: DELETE /logbooks/{id}
     * Only owner can delete, and only DRAFT status allowed.
     */
    public function destroy(Logbook $logbook)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($logbook->user_id !== $user->id) {
            abort(403);
        }

        if ($logbook->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya logbook DRAFT yang dapat dihapus'], 400);
        }

        $logbook->delete();

        return response()->json(['message' => 'Logbook berhasil dihapus']);
    }

    private function canAccessLogbook(User $actor, Logbook $logbook): bool
    {
        if ($actor->isPrivileged()) {
            return true;
        }

        if ($logbook->user_id === $actor->id) {
            return true;
        }

        if ($actor->hasSubordinates()) {
            return $logbook->user?->manager_id === $actor->id;
        }

        return false;
    }
}
