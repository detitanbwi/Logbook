<?php

use App\Models\KpiMaster;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('audit log is created when user is created', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/users', [
        'nama' => 'Audit Test User',
        'email' => 'audit@test.com',
        'npp' => '88888888',
        'role' => 'STAFF',
        'password' => 'password123',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('audit_logs', [
        'table_name' => 'users',
        'action' => 'created',
        'performed_by' => $admin->id,
    ]);
});

test('audit log is created when kpi master is updated', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $kpi = KpiMaster::create(['nama' => 'KPI for Audit', 'status_aktif' => true]);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/v1/kpi/master/{$kpi->id}", [
        'nama' => 'Updated KPI for Audit',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'table_name' => 'kpi_masters',
        'record_id' => $kpi->id,
        'action' => 'updated',
        'performed_by' => $admin->id,
    ]);
});

test('audit log is created when kpi master is deleted', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $kpi = KpiMaster::create(['nama' => 'KPI to Delete', 'status_aktif' => true]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/kpi/master/{$kpi->id}");

    $response->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'table_name' => 'kpi_masters',
        'record_id' => $kpi->id,
        'action' => 'deleted',
        'performed_by' => $admin->id,
    ]);
});

test('superadmin can view audit logs', function () {
    $superAdmin = User::factory()->create(['role' => 'SUPERADMIN']);
    Sanctum::actingAs($superAdmin);

    // Create a log indirectly by acting as admin and doing something
    $this->postJson('/api/v1/users', [
        'nama' => 'Audit Test User 2',
        'email' => 'audit2@test.com',
        'npp' => '88888889',
        'role' => 'STAFF',
        'password' => 'password123',
    ]);

    $response = $this->getJson('/api/v1/audit-logs');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'table_name', 'action', 'performed_by', 'performed_at'],
            ],
            'meta' => ['current_page', 'total'],
        ]);
});

test('admin cannot view audit logs', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/audit-logs');
    $response->assertStatus(403);
});

test('staff cannot view audit logs', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    Sanctum::actingAs($staff);

    $response = $this->getJson('/api/v1/audit-logs');
    $response->assertStatus(403);
});
