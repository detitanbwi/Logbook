<?php

namespace Tests\Feature\Api\V1;

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\User;
use App\Services\DailySummaryService;

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

it('summary endpoints support date filters and zero-target period fallback', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-10',
        'total_logbooks' => 1,
        'submitted_logbooks' => 0,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 120,
        'total_kpi' => 1,
        'target_angka_total' => 0,
        'capaian_angka_total' => 0,
        'progress_percent' => 0,
    ]);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-20',
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

    $filtered = $this->actingAs($manager)->getJson('/api/v1/summaries/daily?date_from=2026-03-15&date_to=2026-03-31');
    $filtered->assertOk()
        ->assertJsonCount(1, 'data');

    expect((string) $filtered->json('data.0.tanggal'))->toStartWith('2026-03-20');

    $period = $this->actingAs($manager)->getJson('/api/v1/summaries/period?date_from=2026-03-10&date_to=2026-03-10');
    $period->assertOk()
        ->assertJsonPath('target_angka_total', 0)
        ->assertJsonPath('capaian_angka_total', 0)
        ->assertJsonPath('progress_percent', 0);
});

it('team daily returns empty data for admin and forbidden for non-manager staff', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $staff = User::factory()->create(['role' => 'STAFF']);

    $adminResponse = $this->actingAs($admin)->getJson('/api/v1/summaries/team/daily');
    $adminResponse->assertOk()->assertJsonCount(0, 'data');

    $this->actingAs($staff)
        ->getJson('/api/v1/summaries/team/daily')
        ->assertForbidden();
});

it('recalculates summaries for both old and new date when logbook date changes', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create(['nama' => 'Task KPI', 'target_angka' => 10, 'satuan' => 'task']);

    // Create logbook on March 10
    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-10',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'status' => 'ACCEPTED',
        'lokasi' => 'office',
    ]);

    // Create KPI detail - this triggers LogbookKpiDetailObserver which rebuilds summaries
    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 10,
        'capaian_angka' => 5,
        'satuan' => 'task',
    ]);

    // Refresh logbook to get latest kpiDetails
    $logbook->refresh();
    $logbook->load('kpiDetails');

    // Manually trigger summary rebuild to ensure it works
    $summaryService = app(DailySummaryService::class);
    $summaryService->syncForLogbook($logbook);

    // Verify summary exists for March 10
    $march10Summary = DailyStaffSummary::where('user_id', $staff->id)
        ->whereDate('tanggal', '2026-03-10')
        ->first();
    expect($march10Summary)->not->toBeNull();
    expect($march10Summary->total_logbooks)->toBe(1);
    expect((float) $march10Summary->capaian_angka_total)->toBe(5.0);

    // Change logbook date from March 10 to March 15
    $logbook->update(['tanggal' => '2026-03-15']);

    // Verify March 10 summary is deleted (no more logbooks on that date)
    $march10After = DailyStaffSummary::where('user_id', $staff->id)
        ->whereDate('tanggal', '2026-03-10')
        ->first();
    expect($march10After)->toBeNull();

    // Verify March 15 summary now has the logbook
    $march15Summary = DailyStaffSummary::where('user_id', $staff->id)
        ->whereDate('tanggal', '2026-03-15')
        ->first();
    expect($march15Summary)->not->toBeNull();
    expect($march15Summary->total_logbooks)->toBe(1);
    expect($march15Summary->accepted_logbooks)->toBe(1);
});

it('recalculates kpi summaries when logbook date changes', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);
    $kpi = KpiMaster::factory()->create(['nama' => 'Sales KPI', 'target_angka' => 20, 'satuan' => 'unit']);

    // Create logbook on March 5
    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-05',
        'start_kerja' => '09:00:00',
        'end_kerja' => '18:00:00',
        'status' => 'ACCEPTED',
        'lokasi' => 'office',
    ]);

    // Create KPI detail
    $logbook->kpiDetails()->create([
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 20,
        'capaian_angka' => 15,
        'satuan' => 'unit',
    ]);

    // Refresh logbook to get latest kpiDetails
    $logbook->refresh();
    $logbook->load('kpiDetails');

    // Manually trigger summary rebuild to ensure it works
    $summaryService = app(DailySummaryService::class);
    $summaryService->syncForLogbook($logbook);

    // Verify KPI summary exists for March 5
    $march5KpiSummary = DailyKpiSummary::where('user_id', $staff->id)
        ->where('kpi_id', $kpi->id)
        ->whereDate('tanggal', '2026-03-05')
        ->first();
    expect($march5KpiSummary)->not->toBeNull();
    expect((float) $march5KpiSummary->capaian_angka_total)->toBe(15.0);

    // Change logbook date to March 12
    $logbook->update(['tanggal' => '2026-03-12']);

    // Verify March 5 KPI summary is deleted (no more KPIs on that date)
    $march5After = DailyKpiSummary::where('user_id', $staff->id)
        ->where('kpi_id', $kpi->id)
        ->whereDate('tanggal', '2026-03-05')
        ->first();
    expect($march5After)->toBeNull();

    // Verify March 12 now has the KPI summary
    $march12KpiSummary = DailyKpiSummary::where('user_id', $staff->id)
        ->where('kpi_id', $kpi->id)
        ->whereDate('tanggal', '2026-03-12')
        ->first();
    expect($march12KpiSummary)->not->toBeNull();
    expect((float) $march12KpiSummary->capaian_angka_total)->toBe(15.0);
});
