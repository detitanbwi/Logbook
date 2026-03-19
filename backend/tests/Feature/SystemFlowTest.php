<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('end-to-end system flow: from creation to logbook review', function () {
    // 1. Admin creates Manager and Staff, and a KPI Master.
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Sanctum::actingAs($admin);

    // Create Manager
    $managerData = [
        'name' => 'Manager 1',
        'email' => 'manager1@test.com',
        'nip' => '198001012000011002',
        'role' => 'MANAGER',
        'password' => 'password123',
    ];
    $response = $this->postJson('/api/v1/users', $managerData);
    $response->assertStatus(201);
    $managerId = $response->json('id');

    // Create Staff
    $staffData = [
        'name' => 'Staff 1',
        'email' => 'staff1@test.com',
        'nip' => '199001012000011003',
        'role' => 'STAFF',
        'manager_id' => $managerId,
        'password' => 'password123',
    ];
    $response = $this->postJson('/api/v1/users', $staffData);
    $response->assertStatus(201);
    $staffId = $response->json('id');

    // Create Master KPI
    $kpiData = [
        'nama' => 'Fix Core Backend Bugs',
        'status_aktif' => true,
    ];
    $response = $this->postJson('/api/v1/kpi/master', $kpiData);
    $response->assertStatus(201);
    $kpiId = $response->json('id');

    // 2. Manager assigns KPI to Staff.
    $manager = User::find($managerId);
    Sanctum::actingAs($manager);

    $assignmentData = [
        'user_id' => $staffId,
        'kpi_id' => $kpiId,
    ];
    $response = $this->postJson('/api/v1/kpi/assignments', $assignmentData);
    $response->assertStatus(201);

    // 3. Staff starts work (Check-in), verifying LogbookKpiDetail copies assigned KPIs.
    $staff = User::find($staffId);
    Sanctum::actingAs($staff);

    Carbon::setTestNow(Carbon::create(2026, 3, 19, 8, 30, 0));

    $startData = [
        'gps_location_start' => json_encode(['lat' => -6.2, 'lng' => 106.8]),
    ];
    $response = $this->postJson('/api/v1/logbooks/start', $startData);
    $response->assertStatus(201);

    $logbookId = $response->json('id');
    $this->assertDatabaseHas('logbooks', [
        'id' => $logbookId,
        'status' => 'DRAFT',
    ]);

    // Verify KPI is copied
    $response->assertJsonPath('kpi_details.0.kpi_id', $kpiId);
    $response->assertJsonPath('kpi_details.0.is_finished', false);
    $detailId = $response->json('kpi_details.0.id');

    // 4. Staff completes a KPI and submits logbook.
    $toggleData = ['is_finished' => true];
    $response = $this->patchJson("/api/v1/logbooks/{$logbookId}/kpi/{$detailId}/toggle", $toggleData);
    $response->assertStatus(200)->assertJsonPath('is_finished', true);

    Carbon::setTestNow(Carbon::create(2026, 3, 19, 17, 30, 0));

    $submitData = [
        'gps_location_end' => json_encode(['lat' => -6.21, 'lng' => 106.81]),
        'gambar_bukti' => [\Illuminate\Http\UploadedFile::fake()->image('proof.jpg')],
    ];
    $response = $this->postJson("/api/v1/logbooks/{$logbookId}/submit", $submitData);
    $response->assertStatus(200)->assertJsonPath('status', 'SUBMITTED');

    // 5. Manager rates the logbook.
    Sanctum::actingAs($manager);

    $rateData = ['rating' => 5];
    $response = $this->putJson("/api/v1/logbooks/{$logbookId}/rate", $rateData);
    $response->assertStatus(200)->assertJsonPath('status', 'REVIEWED');

    // 6. Admin and Manager verify analytics dashboard.
    $response = $this->getJson('/api/v1/dashboard/manager');
    $response->assertStatus(200);
    // Manager should see the subordinate with 100% completion rate (1 out of 1)
    $response->assertJsonPath('data.subordinates.0.id', $staffId)
        ->assertJsonPath('data.subordinates.0.completed_kpi', 1)
        ->assertJsonPath('data.subordinates.0.total_kpi', 1)
        ->assertJsonPath('data.subordinates.0.completion_rate', 100);

    Sanctum::actingAs($admin);
    $response = $this->getJson('/api/v1/dashboard/admin');
    $response->assertStatus(200);
    // Admin sees total logbooks
    $response->assertJsonPath('data.total_logbooks_this_month', 1);

    Carbon::setTestNow(); // reset time
});
