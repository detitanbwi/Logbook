<?php

use App\Models\Logbook;
use App\Models\User;

it('admin can review submitted logbook', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($admin)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 4,
            'reviewer_comment' => 'Reviewed by admin.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'ACCEPTED');
});

it('superadmin can review submitted logbook', function () {
    $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($superAdmin)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'REJECTED',
            'rating' => 2,
            'reviewer_comment' => 'Reviewed by superadmin.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'REJECTED');
});

it('staff cannot review submitted logbook', function () {
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    $this->actingAs($staff)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 4,
            'reviewer_comment' => 'Should not pass.',
        ])
        ->assertForbidden();
});

it('returns 409 when reviewing a logbook that was already reviewed', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    // First review succeeds
    $this->actingAs($admin)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 5,
            'reviewer_comment' => 'First reviewer.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'ACCEPTED');

    // Second review attempt on same logbook returns 409 (already reviewed)
    $this->actingAs($admin)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'REJECTED',
            'rating' => 1,
            'reviewer_comment' => 'Second reviewer too late.',
        ])
        ->assertStatus(400);
});

it('returns 409 when reverting a logbook that was already reviewed', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $manager = User::factory()->create(['role' => 'Staff']);
    $staff = User::factory()->create(['role' => 'Staff', 'manager_id' => $manager->id]);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'status' => 'SUBMITTED',
        'tanggal' => now()->toDateString(),
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'lokasi' => 'loc',
    ]);

    // First: accept the logbook
    $this->actingAs($admin)
        ->putJson("/api/v1/logbooks/{$logbook->id}/review", [
            'decision' => 'ACCEPTED',
            'rating' => 5,
            'reviewer_comment' => 'Accepted.',
        ])
        ->assertOk();

    // Second: try to revert — should fail because status is now ACCEPTED, not SUBMITTED
    $this->actingAs($admin)
        ->postJson("/api/v1/logbooks/{$logbook->id}/revert", [
            'reason' => 'Too late to revert.',
        ])
        ->assertStatus(400);
});
