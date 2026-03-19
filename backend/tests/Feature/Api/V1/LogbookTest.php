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
        ->assertJsonPath('data.status', 'DRAFT')
        ->assertJsonPath('data.tanggal', '2026-03-19')
        ->assertJsonPath('data.start_kerja', '08:30')
        ->assertJsonPath('data.lokasi', '-6.200000,106.816666')
        ->assertJsonCount(1, 'data.kpi_details');

    $this->assertDatabaseHas('logbooks', [
        'user_id' => $staff->id,
        'status' => 'DRAFT',
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

it('staff can update kpi progress in draft logbook', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'DRAFT',
        'start_kerja' => '08:00:00',
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
        'status' => 'DRAFT',
        'start_kerja' => '08:00:00',
        'lokasi' => 'loc',
    ]);

    $kpi = KpiMaster::factory()->create();
    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 0,
    ]);

    $response = $this->actingAs($staff)->postJson("/api/v1/logbooks/{$logbook->id}/submit", [
        'end_kerja' => '16:00',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'SUBMITTED');

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'SUBMITTED',
        'lokasi' => 'loc',
    ]);

    $notification = Notification::where('user_id', $manager->id)->latest()->first();
    expect($notification)->toBeNull();
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
        'status' => 'DRAFT',
        'start_kerja' => '08:00:00',
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

it('uploads and deletes kpi attachment in draft logbook', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create();

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => now()->toDateString(),
        'status' => 'DRAFT',
        'start_kerja' => '08:00:00',
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

    $this->actingAs($manager)
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert")
        ->assertStatus(404);

    $this->actingAs($staff)
        ->patchJson("/api/v1/logbooks/{$logbook->id}/kpi/{$detail->id}/toggle", ['is_finished' => true])
        ->assertStatus(404);
});
