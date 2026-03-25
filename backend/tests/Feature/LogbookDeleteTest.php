<?php

use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'STAFF']);
    $this->staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $this->manager->id]);
});

test('staff can delete own SUBMITTED logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $logbookId = $logbook->id;

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil dihapus');

    $this->assertSoftDeleted('logbooks', ['id' => $logbookId]);
});

test('staff can delete own REJECTED logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'REJECTED',
    ]);

    $logbookId = $logbook->id;

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil dihapus');

    $this->assertSoftDeleted('logbooks', ['id' => $logbookId]);
});

test('staff cannot delete ACCEPTED logbook', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'ACCEPTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(400);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

test('staff cannot delete another users logbook', function () {
    $otherStaff = User::factory()->create(['role' => 'STAFF']);
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $otherStaff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

test('deleting logbook also deletes associated kpi details', function () {
    Sanctum::actingAs($this->staff);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $detail1 = LogbookKpiDetail::factory()->create([
        'logbook_id' => $logbook->id,
        'kpi_nama' => 'KPI 1',
        'target_angka' => 10,
        'capaian_angka' => 5,
    ]);

    $detail2 = LogbookKpiDetail::factory()->create([
        'logbook_id' => $logbook->id,
        'kpi_nama' => 'KPI 2',
        'target_angka' => 20,
        'capaian_angka' => 10,
    ]);

    $logbookId = $logbook->id;
    $detail1Id = $detail1->id;
    $detail2Id = $detail2->id;

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('logbooks', ['id' => $logbookId]);
    $this->assertSoftDeleted('logbook_kpi_details', ['id' => $detail1Id]);
    $this->assertSoftDeleted('logbook_kpi_details', ['id' => $detail2Id]);
});

test('manager cannot delete subordinate SUBMITTED logbook', function () {
    Sanctum::actingAs($this->manager);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

test('unauthenticated user cannot delete logbook', function () {
    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(401);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

// =============================================================================
// Admin Actor Tests
// =============================================================================

test('admin cannot delete staff logbook', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

test('admin can delete own SUBMITTED logbook', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $logbook = Logbook::factory()->create([
        'user_id' => $admin->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $logbookId = $logbook->id;

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil dihapus');

    $this->assertSoftDeleted('logbooks', ['id' => $logbookId]);
});

// =============================================================================
// SuperAdmin Actor Tests
// =============================================================================

test('superadmin cannot delete staff logbook', function () {
    $superadmin = User::factory()->create(['role' => 'SUPERADMIN']);
    Sanctum::actingAs($superadmin);

    $logbook = Logbook::factory()->create([
        'user_id' => $this->staff->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('logbooks', ['id' => $logbook->id]);
});

test('superadmin can delete own SUBMITTED logbook', function () {
    $superadmin = User::factory()->create(['role' => 'SUPERADMIN']);
    Sanctum::actingAs($superadmin);

    $logbook = Logbook::factory()->create([
        'user_id' => $superadmin->id,
        'tanggal' => '2026-03-20',
        'start_kerja' => '08:00',
        'end_kerja' => '17:00',
        'status' => 'SUBMITTED',
    ]);

    $logbookId = $logbook->id;

    $response = $this->deleteJson("/api/v1/logbooks/{$logbook->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook berhasil dihapus');

    $this->assertSoftDeleted('logbooks', ['id' => $logbookId]);
});
