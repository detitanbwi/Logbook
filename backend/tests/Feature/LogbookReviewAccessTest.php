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
