<?php

namespace Tests\Feature\Api\V1;

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\KpiMaster;
use App\Models\User;

it('summary endpoints enforce access and return basic payloads', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $otherStaff = User::factory()->create(['role' => 'STAFF']);

    $kpi = KpiMaster::factory()->create(['nama' => 'KPI Harian', 'target_angka' => 10, 'satuan' => 'unit']);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-19',
        'total_logbooks' => 2,
        'submitted_logbooks' => 1,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 420,
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 10,
        'progress_percent' => 50,
    ]);

    DailyKpiSummary::create([
        'user_id' => $staff->id,
        'kpi_id' => $kpi->id,
        'tanggal' => '2026-03-19',
        'kpi_nama' => 'KPI Harian',
        'satuan' => 'unit',
        'target_angka_total' => 20,
        'capaian_angka_total' => 10,
        'progress_percent' => 50,
        'total_lampiran' => 1,
    ]);

    $staffDaily = $this->actingAs($staff)->getJson('/api/v1/summaries/daily');
    $staffDaily->assertOk()
        ->assertJsonPath('data.0.user_id', $staff->id);
    expect((float) $staffDaily->json('data.0.progress_percent'))->toBe(50.0);

    $managerDaily = $this->actingAs($manager)->getJson('/api/v1/summaries/daily');
    $managerDaily->assertOk()->assertJsonPath('data.0.user_id', $staff->id);

    $managerOtherUser = $this->actingAs($manager)->getJson("/api/v1/summaries/daily/{$otherStaff->id}");
    $managerOtherUser->assertForbidden();

    $adminPeriod = $this->actingAs($admin)->getJson('/api/v1/summaries/period?date_from=2026-03-01&date_to=2026-03-31');
    $adminPeriod->assertOk()
        ->assertJsonPath('total_logbooks', 2)
        ->assertJsonPath('target_angka_total', 20)
        ->assertJsonPath('capaian_angka_total', 10)
        ->assertJsonPath('progress_percent', 50);

    $kpiDaily = $this->actingAs($staff)->getJson('/api/v1/summaries/kpi/daily?tanggal=2026-03-19');
    $kpiDaily->assertOk()
        ->assertJsonPath('data.0.kpi_id', $kpi->id)
        ->assertJsonPath('data.0.total_lampiran', 1);

    $kpiPeriod = $this->actingAs($admin)->getJson('/api/v1/summaries/kpi/period?date_from=2026-03-01&date_to=2026-03-31');
    $kpiPeriod->assertOk()
        ->assertJsonPath('items.0.kpi_id', $kpi->id);
    expect((float) $kpiPeriod->json('items.0.progress_percent'))->toBe(50.0);
});
