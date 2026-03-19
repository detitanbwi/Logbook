<?php

use App\Models\KpiMaster;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin and manager can list kpi masters', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);

    $kpi = KpiMaster::create(['nama' => 'Test KPI', 'status_aktif' => true]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/kpi/master');

    $response->assertStatus(200)
        ->assertJsonFragment(['nama' => 'Test KPI']);
});

test('admin can create kpi master', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/kpi/master', [
        'nama' => 'New KPI',
        'status_aktif' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['nama' => 'New KPI']);
});

test('manager cannot create kpi master', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    Sanctum::actingAs($manager);

    $response = $this->postJson('/api/v1/kpi/master', [
        'nama' => 'New KPI',
        'status_aktif' => true,
    ]);

    $response->assertStatus(403);
});

test('admin can update kpi master', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $kpi = KpiMaster::create(['nama' => 'Old KPI', 'status_aktif' => true]);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/kpi/master/{$kpi->id}", [
        'nama' => 'Updated KPI',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['nama' => 'Updated KPI']);
});

test('admin can delete kpi master', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $kpi = KpiMaster::create(['nama' => 'To Delete KPI', 'status_aktif' => true]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/kpi/master/{$kpi->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($kpi);
});
