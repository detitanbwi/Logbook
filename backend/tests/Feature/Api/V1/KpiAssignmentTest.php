<?php

namespace Tests\Feature\Api\V1;

use App\Models\KpiMaster;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserKpiAssignment;

it('manager can assign kpi to subordinate', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    $response = $this->actingAs($manager)->postJson('/api/v1/kpi/assignments', [
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
    ]);

    $response->assertStatus(201);

    $assignmentId = $response->json('id');

    $this->assertDatabaseHas('user_kpi_assignments', [
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $manager->id,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'title' => 'KPI Baru Ditugaskan',
        'type' => 'KPI_ASSIGNMENT',
        'reference_id' => null,
    ]);

    $notification = Notification::where('user_id', $staff->id)->latest()->first();
    expect($notification)->not->toBeNull();
    expect($notification?->target_path)->toBe('/staff/logbook');
    expect($notification?->target_params)->toMatchArray(['assignment_id' => null]);
});

it('manager cannot assign kpi to non-subordinate', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $otherManager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $otherManager->id]);
    $kpi = KpiMaster::factory()->create();

    $response = $this->actingAs($manager)->postJson('/api/v1/kpi/assignments', [
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
    ]);

    $response->assertStatus(403);
});

it('staff can see their own kpis', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $kpi = KpiMaster::factory()->create();

    UserKpiAssignment::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'assigned_by' => $manager->id,
    ]);

    $response = $this->actingAs($staff)->getJson('/api/v1/kpi/me');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.kpi_id', $kpi->id);
});
