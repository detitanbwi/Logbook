<?php

namespace Tests\Feature\Api\V1;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserKpiAssignment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

it('staff can start logbook and it copies active kpis', function () {
    Carbon::setTestNow('2026-03-19 08:30:00');

    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    UserKpiAssignment::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $manager->id,
    ]);

    $response = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:30',
        'lokasi' => '-6.200000,106.816666',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'SUBMITTED')
        ->assertJsonPath('data.tanggal', '2026-03-19')
        ->assertJsonPath('data.start_kerja', '08:30')
        ->assertJsonPath('data.lokasi', '-6.200000,106.816666')
        ->assertJsonCount(1, 'data.details');

    $this->assertDatabaseHas('logbooks', [
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
    ]);

    $logbookId = $response->json('data.id');

    $this->assertDatabaseHas('logbook_kpi_details', [
        'logbook_id' => $logbookId,
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => $kpi->target_angka,
        'capaian_angka' => 0,
    ]);

    Carbon::setTestNow();
});

it('staff can update kpi progress in submitted logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $kpi = KpiMaster::factory()->create();
    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 5,
        'capaian_angka' => 0,
    ]);

    $response = $this->actingAs($staff)->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/progress", [
        'capaian_angka' => 5,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('capaian_angka', 5);

    $this->assertDatabaseHas('logbook_kpi_details', [
        'id' => $detail->id,
        'capaian_angka' => 5,
    ]);
});

it('staff can submit logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $kpi = KpiMaster::factory()->create();
    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 1,
    ]);

    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/submit");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'SUBMITTED');

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'SUBMITTED',
        'lokasi' => 'loc',
    ]);

    $notification = Notification::where('user_id', $manager->id)->latest()->first();
    expect($notification)->not->toBeNull();
    expect($notification?->type)->toBe('LOGBOOK_SUBMITTED');
    expect($notification?->reference_id)->toBe($logbook->id);
});

it('manager can review submitted logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $response = $this->actingAs($manager)->putJson("/api/v1/logbooks/{$logbook->id}/review", [
        'decision' => 'ACCEPTED',
        'rating' => 4,
        'reviewer_comment' => 'Bagus.',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'ACCEPTED')
        ->assertJsonPath('data.rating', 4);

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'ACCEPTED',
        'rating' => 4,
        'reviewed_by' => $manager->id,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'title' => 'Logbook Accepted',
        'type' => 'LOGBOOK_ACCEPTED',
        'reference_id' => $logbook->id,
    ]);

    $notification = Notification::where('user_id', $staff->id)->latest()->first();
    expect($notification)->not->toBeNull();
    expect($notification?->target_path)->toBe('/staff/history');
    expect($notification?->target_params)->toMatchArray(['logbook_id' => $logbook->id]);
});

it('manager can reject submitted logbook and create routeable notification', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $response = $this->actingAs($manager)->putJson("/api/v1/logbooks/{$logbook->id}/review", [
        'decision' => 'REJECTED',
        'rating' => 2,
        'reviewer_comment' => 'Perlu revisi.',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'REJECTED');

    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'title' => 'Logbook Rejected',
        'type' => 'LOGBOOK_REJECTED',
        'reference_id' => $logbook->id,
    ]);

    $notification = Notification::where('user_id', $staff->id)->latest()->first();
    expect($notification)->not->toBeNull();
    expect($notification?->target_path)->toBe('/staff/history');
    expect($notification?->target_params)->toMatchArray(['logbook_id' => $logbook->id]);
});

it('validates manual time input and allows multiple logbooks in one day', function () {
    Carbon::setTestNow('2026-03-19 09:00:00');

    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $kpi = KpiMaster::factory()->create();
    UserKpiAssignment::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $manager->id,
    ]);

    $tooEarly = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '06:59',
        'lokasi' => 'lokasi-a',
    ]);

    $tooEarly->assertStatus(422)
        ->assertJsonPath('message', 'Jam mulai minimal 07:00');

    $invalidEnd = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:30',
        'end_kerja' => '08:30',
        'lokasi' => 'lokasi-a',
    ]);

    $invalidEnd->assertStatus(422)
        ->assertJsonPath('message', 'Jam selesai harus lebih besar dari jam mulai');

    $first = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:30',
        'lokasi' => 'lokasi-a',
    ]);

    $first->assertStatus(201)
        ->assertJsonPath('data.tanggal', '2026-03-19')
        ->assertJsonPath('data.start_kerja', '08:30');

    $second = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '14:00',
        'lokasi' => 'lokasi-b',
    ]);

    $second->assertStatus(201)
        ->assertJsonPath('data.tanggal', '2026-03-19')
        ->assertJsonPath('data.start_kerja', '14:00');

    expect(Logbook::where('user_id', $staff->id)->whereDate('tanggal', '2026-03-19')->count())->toBe(2);

    Carbon::setTestNow();
});

it('updates kpi numeric progress via progress endpoint', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 12,
        'capaian_angka' => 0,
    ]);

    $response = $this->actingAs($staff)->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/progress", [
        'capaian_angka' => 7.5,
    ]);

    $response->assertOk()
        ->assertJsonPath('capaian_angka', 7.5)
        ->assertJsonPath('target_angka', 12);

    $this->assertDatabaseHas('logbook_kpi_details', [
        'id' => $detail->id,
        'capaian_angka' => 7.5,
    ]);
});

it('uploads and deletes kpi attachment in submitted logbook', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $upload = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment", [
        'lampiran_file' => UploadedFile::fake()->create('evidence.pdf', 100, 'application/pdf'),
    ]);

    $upload->assertOk()
        ->assertJsonPath('data.id', $detail->id);

    $storedPath = $upload->json('data.lampiran_file');
    expect($storedPath)->not->toBeNull();
    expect($storedPath)->toStartWith('logbook-kpi-attachments/');
    Storage::disk('public')->assertExists($storedPath);

    $delete = $this->actingAs($staff)->deleteJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment");

    $delete->assertOk()
        ->assertJsonPath('data.lampiran_file', null);

    Storage::disk('public')->assertMissing($storedPath);
});

it('manager review supports accepted and rejected with reviewer_comment', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $accepted = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $acceptResponse = $this->actingAs($manager)->putJson("/api/v1/logbooks/{$accepted->id}/review", [
        'decision' => 'ACCEPTED',
        'rating' => 5,
        'reviewer_comment' => 'Bagus dan lengkap.',
    ]);

    $acceptResponse->assertOk()
        ->assertJsonPath('data.status', 'ACCEPTED')
        ->assertJsonPath('data.reviewer_comment', 'Bagus dan lengkap.');

    $this->assertDatabaseHas('logbooks', [
        'id' => $accepted->id,
        'status' => 'ACCEPTED',
        'reviewer_comment' => 'Bagus dan lengkap.',
        'reviewed_by' => $manager->id,
    ]);

    $rejected = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $rejectResponse = $this->actingAs($manager)->putJson("/api/v1/logbooks/{$rejected->id}/review", [
        'decision' => 'REJECTED',
        'rating' => 2,
        'reviewer_comment' => 'Perlu perbaikan angka KPI.',
    ]);

    $rejectResponse->assertOk()
        ->assertJsonPath('data.status', 'REJECTED')
        ->assertJsonPath('data.reviewer_comment', 'Perlu perbaikan angka KPI.');

    $this->assertDatabaseHas('logbooks', [
        'id' => $rejected->id,
        'status' => 'REJECTED',
        'reviewer_comment' => 'Perlu perbaikan angka KPI.',
        'reviewed_by' => $manager->id,
    ]);
});

it('manager review requires reviewer comment', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 4,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('reviewer_comment');
});

it('manager without subordinates cannot review logbook', function () {
    $managerLike = User::factory()->create(['role' => 'MANAGER']);
    $realManager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $realManager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($managerLike)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 4,
            'reviewer_comment' => 'ok',
        ])
        ->assertForbidden();
});

it('staff can upload attachment to submitted logbook', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment", [
            'lampiran_file' => UploadedFile::fake()->create('evidence.pdf', 50, 'application/pdf'),
        ])
        ->assertOk();
});

it('staff with subordinates can still create their own logbook', function () {
    $managerStaff = User::factory()->create(['role' => 'STAFF']);
    User::factory()->create(['role' => 'STAFF', 'manager_id' => $managerStaff->id]);

    $kpi = KpiMaster::factory()->create();
    UserKpiAssignment::create([
        'user_id' => $managerStaff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $managerStaff->id,
    ]);

    $response = $this->actingAs($managerStaff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'lokasi' => '-6.2,106.8',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'SUBMITTED');
});

it('submit requires at least one kpi progress greater than zero', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/submit")
        ->assertStatus(422)
        ->assertJsonPath('message', 'Minimal satu KPI harus memiliki capaian lebih dari 0 sebelum submit');
});

it('submit notifies manager when subordinate submits', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 2,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/submit")
        ->assertOk()
        ->assertJsonPath('data.status', 'SUBMITTED');

    $this->assertDatabaseHas('notifications', [
        'user_id' => $manager->id,
        'type' => 'LOGBOOK_SUBMITTED',
        'reference_id' => $logbook->id,
    ]);
});

it('returns computed duration breakdown for logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-20',
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($staff)
        ->getJson("/api/v1/logbooks/{$logbook->id}/duration")
        ->assertOk()
        ->assertJsonPath('gross_work_minutes', 540)
        ->assertJsonPath('break_overlap_minutes', 60)
        ->assertJsonPath('net_work_minutes', 480);
});

it('legacy compatibility endpoints are removed', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $kpi = KpiMaster::factory()->create();
    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 5,
        'capaian_angka' => 0,
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/rate", ['rating' => 4])
        ->assertStatus(404);

    $this->actingAs($staff)
        ->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/toggle", ['is_finished' => true])
        ->assertStatus(404);
});

// ============================================================
// Edge Case Tests for Validation Boundaries
// ============================================================

it('allows start time exactly at 07:00 boundary', function () {
    Carbon::setTestNow('2026-03-19 08:00:00');

    $staff = User::factory()->create(['role' => 'STAFF']);

    $kpi = KpiMaster::factory()->create();
    UserKpiAssignment::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $staff->id,
    ]);

    $response = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '07:00',
        'lokasi' => 'office',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.start_kerja', '07:00');

    Carbon::setTestNow();
});

it('rejects end time earlier than start time', function () {
    Carbon::setTestNow('2026-03-19 10:00:00');

    $staff = User::factory()->create(['role' => 'STAFF']);

    $response = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '09:00',
        'end_kerja' => '08:00',
        'lokasi' => 'office',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Jam selesai harus lebih besar dari jam mulai');

    Carbon::setTestNow();
});

it('rejects rating below 1', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 0,
            'reviewer_comment' => 'Comment here',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('rating');
});

it('rejects rating above 5', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 6,
            'reviewer_comment' => 'Comment here',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('rating');
});

it('rejects invalid decision value REVIEWED', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'REVIEWED',
            'rating' => 4,
            'reviewer_comment' => 'Looks good',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('decision');
});

it('blocks attachment upload in accepted status', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'ACCEPTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment", [
            'lampiran_file' => UploadedFile::fake()->create('blocked.pdf', 50, 'application/pdf'),
        ])
        ->assertStatus(400);
});

it('allows attachment upload in rejected status', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'REJECTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment", [
            'lampiran_file' => UploadedFile::fake()->create('revision.pdf', 50, 'application/pdf'),
        ])
        ->assertOk();
});

it('allows attachment deletion in submitted status', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
        'lampiran_file' => 'logbook-kpi-attachments/existing.pdf',
    ]);

    $this->actingAs($staff)
        ->deleteJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment")
        ->assertOk();
});

it('blocks attachment deletion in accepted status', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'ACCEPTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
        'lampiran_file' => 'logbook-kpi-attachments/existing.pdf',
    ]);

    $this->actingAs($staff)
        ->deleteJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/attachment")
        ->assertStatus(400);
});

it('staff can update kpi progress in submitted status', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $this->actingAs($staff)
        ->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/progress", [
            'capaian_angka' => 5,
        ])
        ->assertStatus(200)
        ->assertJsonPath('capaian_angka', 5);
});

it('blocks kpi progress update in accepted status', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'ACCEPTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/progress", [
            'capaian_angka' => 8,
        ])
        ->assertStatus(400);
});

it('rejects negative capaian_angka value', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $detail = $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $this->actingAs($staff)
        ->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/progress", [
            'capaian_angka' => -5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('capaian_angka');
});

it('staff can re-submit already submitted logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
    ]);

    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/submit")
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'SUBMITTED');
});

it('staff can re-submit rejected logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'REJECTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'lokasi' => 'loc',
        'rating' => 2,
        'reviewed_by' => $manager->id,
    ]);

    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/submit")
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'SUBMITTED');
});

it('cannot submit accepted logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'ACCEPTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/submit")
        ->assertStatus(400);
});

it('cannot review already accepted logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'ACCEPTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
        'rating' => 5,
        'reviewed_by' => $manager->id,
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'REJECTED',
            'rating' => 2,
            'reviewer_comment' => 'Changed my mind',
        ])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Only SUBMITTED logbooks can be reviewed.');
});

it('cannot review already rejected logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'REJECTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
        'rating' => 2,
        'reviewed_by' => $manager->id,
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 4,
            'reviewer_comment' => 'Actually it was good',
        ])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Only SUBMITTED logbooks can be reviewed.');
});

it('manager can list subordinate logbooks via GET /logbooks', function () {
    $manager = User::factory()->create(['role' => 'Staff']);
    $subordinate = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);
    $otherStaff = User::factory()->create(['role' => 'Staff']);

    $subLogbook = Logbook::create([
        'user_id' => $subordinate->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'Office',
    ]);

    $otherLogbook = Logbook::create([
        'user_id' => $otherStaff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'Remote',
    ]);

    $managerLogbook = Logbook::create([
        'user_id' => $manager->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'HQ',
    ]);

    $response = $this->actingAs($manager)->getJson('/api/v1/logbooks');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($subLogbook->id)
        ->toContain($managerLogbook->id);

    expect($ids)->not->toContain($otherLogbook->id);
});

it('staff without subordinates only sees own logbooks', function () {
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'Office',
    ]);

    Logbook::create([
        'user_id' => $manager->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'HQ',
    ]);

    $response = $this->actingAs($staff)->getJson('/api/v1/logbooks');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toHaveCount(1);
});

it('allows logbook start when staff has no KPI assignments', function () {
    Carbon::setTestNow('2026-03-19 09:00:00');

    $staff = User::factory()->create(['role' => 'STAFF']);

    $response = $this->actingAs($staff)->postJson('/api/v1/logbooks/start', [
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:30',
        'lokasi' => '-6.2,106.8',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'SUBMITTED')
        ->assertJsonCount(0, 'data.details');

    Carbon::setTestNow();
});

it('staff can add kpi to an existing submitted logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create([
        'nama' => 'KPI Tambahan',
        'target_angka' => 8,
        'satuan' => 'unit',
    ]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'SUBMITTED',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/kpi", [
        'kpi_id' => $kpi->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.id', $logbook->id)
        ->assertJsonCount(1, 'data.details');

    $this->assertDatabaseHas('logbook_kpi_details', [
        'logbook_id' => $logbook->id,
        'kpi_id' => $kpi->id,
        'kpi_nama' => 'KPI Tambahan',
        'target_angka' => 8,
        'capaian_angka' => 0,
    ]);
});
