<?php

namespace Tests\Feature;

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
        'nama' => 'Manager 1',
        'email' => 'manager1@test.com',
        'npp' => '198001012000011002',
        'role' => 'Staff',
        'password' => 'password123',
    ];
    $response = $this->postJson('/api/v1/users', $managerData);
    $response->assertStatus(201);
    $managerId = $response->json('data.id');

    // Create Staff
    $staffData = [
        'nama' => 'Staff 1',
        'email' => 'staff1@test.com',
        'npp' => '199001012000011003',
        'role' => 'STAFF',
        'manager_id' => $managerId,
        'password' => 'password123',
    ];
    $response = $this->postJson('/api/v1/users', $staffData);
    $response->assertStatus(201);
    $staffId = $response->json('data.id');

    // Create Master KPI
    $kpiData = [
        'nama' => 'Fix Core Backend Bugs',
        'target_angka' => 100,
        'satuan' => 'unit',
        'status_aktif' => true,
    ];
    $response = $this->postJson('/api/v1/kpi/master', $kpiData);
    $response->assertStatus(201);
    $kpiId = $response->json('data.id');

    // 2. Manager assigns KPI to Staff.
    $manager = User::findOrFail($managerId);
    Sanctum::actingAs($manager);

    $assignmentData = [
        'user_id' => $staffId,
        'kpi_id' => $kpiId,
    ];
    $response = $this->postJson('/api/v1/kpi/assignments', $assignmentData);
    $response->assertStatus(201);

    // 3. Staff starts work (Check-in), verifying LogbookKpiDetail copies assigned KPIs.
    $staff = User::findOrFail($staffId);
    Sanctum::actingAs($staff);

    Carbon::setTestNow(Carbon::create(2026, 3, 19, 8, 30, 0));

    $startData = [
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:30',
        'lokasi' => '-6.2,106.8',
    ];
    $response = $this->postJson('/api/v1/logbooks/start', $startData);
    $response->assertStatus(201);
    $response->assertJsonPath('data.lokasi', '-6.2,106.8');

    $logbookId = $response->json('data.id');
    $this->assertDatabaseHas('logbooks', [
        'id' => $logbookId,
        'status' => 'DRAFT',
    ]);

    // Verify KPI is copied
    $response->assertJsonPath('data.details.0.kpi_id', $kpiId);
    $response->assertJsonPath('data.details.0.capaian_angka', 0);
    $detailId = $response->json('data.details.0.id');

    // 4. Staff updates KPI progress and submits logbook.
    $progressData = ['capaian_angka' => 100];
    $response = $this->patchJson("/api/v1/logbooks/{$logbookId}/kpi/{$detailId}/progress", $progressData);
    $response->assertStatus(200)->assertJsonPath('capaian_angka', 100);

    Carbon::setTestNow(Carbon::create(2026, 3, 19, 17, 30, 0));

    // Update end_kerja before submit (per plan spec section 9.2)
    $updateData = ['end_kerja' => '17:30'];
    $response = $this->patchJson("/api/v1/logbooks/{$logbookId}", $updateData);
    $response->assertStatus(200)->assertJsonPath('data.id', $logbookId);

    // Now submit the logbook
    $response = $this->postJson("/api/v1/logbooks/{$logbookId}/submit");
    $response->assertStatus(200)->assertJsonPath('data.status', 'SUBMITTED');

    // 5. Manager reviews the logbook.
    Sanctum::actingAs($manager);

    $reviewData = ['decision' => 'ACCEPTED', 'rating' => 5, 'reviewer_comment' => 'Good work'];
    $response = $this->putJson("/api/v1/logbooks/{$logbookId}/review", $reviewData);
    $response->assertStatus(200)->assertJsonPath('data.status', 'ACCEPTED');

    // 6. Admin and Manager verify analytics dashboard.
    $response = $this->getJson('/api/v1/dashboard/manager');
    $response->assertStatus(200);
    // Manager should see the subordinate with 100% completion rate (1 out of 1)
    $response->assertJsonPath('data.subordinates.0.id', $staffId)
        ->assertJsonPath('data.subordinates.0.capaian_angka_total', 100)
        ->assertJsonPath('data.subordinates.0.target_angka_total', 100)
        ->assertJsonPath('data.subordinates.0.completion_rate', 100);

    Sanctum::actingAs($admin);
    $response = $this->getJson('/api/v1/dashboard/admin');
    $response->assertStatus(200);
    // Admin sees total logbooks
    $response->assertJsonPath('data.total_logbooks_this_month', 1);

    Carbon::setTestNow(); // reset time
});
