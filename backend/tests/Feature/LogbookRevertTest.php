<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\Notification;
use App\Models\User;

it('manager can revert subordinate submitted logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
        'reviewed_by' => $manager->id,
        'reviewed_at' => now(),
        'reviewer_comment' => 'Previous comment',
    ]);

    $response = $this->actingAs($manager)->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
        'reason' => 'Bukti kunjungan kurang lengkap, tambahkan foto lokasi.',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook dikembalikan ke DRAFT')
        ->assertJsonPath('data.id', $logbook->id)
        ->assertJsonPath('data.status', 'DRAFT');

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'DRAFT',
        'reviewed_by' => null,
        'reviewed_at' => null,
        'reviewer_comment' => null,
        'rating' => null,
    ]);
});

it('manager cannot revert non-subordinate logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $otherManager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $otherManager->id]);

    // Make sure $manager has subordinates so they have the manager role
    User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail',
        ])
        ->assertForbidden();
});

it('manager cannot revert draft logbook', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'DRAFT',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($manager)
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail',
        ])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Only SUBMITTED logbooks can be reverted.');
});

it('manager cannot revert accepted logbook', function () {
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
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail',
        ])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Only SUBMITTED logbooks can be reverted.');
});

it('manager cannot revert rejected logbook', function () {
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
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail',
        ])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Only SUBMITTED logbooks can be reverted.');
});

it('staff cannot revert own logbook', function () {
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

    $this->actingAs($staff)
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail - staff cannot revert',
        ])
        ->assertForbidden();
});

it('admin can revert any submitted logbook', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
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

    $response = $this->actingAs($admin)->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
        'reason' => 'Admin reverts for compliance check.',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook dikembalikan ke DRAFT')
        ->assertJsonPath('data.status', 'DRAFT');

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'DRAFT',
    ]);
});

it('superadmin can revert any submitted logbook', function () {
    $superAdmin = User::factory()->create(['role' => 'SUPERADMIN']);
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

    $response = $this->actingAs($superAdmin)->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
        'reason' => 'SuperAdmin audit revert.',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Logbook dikembalikan ke DRAFT')
        ->assertJsonPath('data.status', 'DRAFT');

    $this->assertDatabaseHas('logbooks', [
        'id' => $logbook->id,
        'status' => 'DRAFT',
    ]);
});

it('revert creates notification for staff', function () {
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

    $this->actingAs($manager)->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
        'reason' => 'Bukti kurang lengkap.',
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'type' => 'LOGBOOK_REVERTED',
        'reference_id' => $logbook->id,
    ]);

    $notification = Notification::where('user_id', $staff->id)->latest()->first();
    expect($notification)->not->toBeNull();
    expect($notification?->type)->toBe('LOGBOOK_REVERTED');
    expect($notification?->reference_id)->toBe($logbook->id);
    expect($notification?->target_path)->toBe('/staff/logbook');
    expect($notification?->target_params)->toMatchArray(['logbook_id' => $logbook->id]);
});

it('unauthenticated user cannot revert logbook', function () {
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

    $this->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
        'reason' => 'Should fail - not authenticated',
    ])->assertUnauthorized();
});

it('revert requires reason field', function () {
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
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('reason');
});

it('revert reason cannot exceed 5000 characters', function () {
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
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => str_repeat('a', 5001),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('reason');
});

it('manager without subordinates cannot revert logbook', function () {
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
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Should fail',
        ])
        ->assertForbidden();
});
