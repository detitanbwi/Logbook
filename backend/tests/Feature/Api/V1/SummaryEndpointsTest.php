<?php

namespace Tests\Feature\Api\V1;

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\User;
use App\Services\DailySummaryService;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

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

    Sanctum::actingAs($staff);
    $staffDaily = getJson('/api/v1/summaries/daily');
    $staffDaily->assertOk()
        ->assertJsonPath('data.0.user_id', $staff->id);
    expect((float) $staffDaily->json('data.0.progress_percent'))->toBe(50.0);

    Sanctum::actingAs($manager);
    $managerDaily = getJson('/api/v1/summaries/daily');
    $managerDaily->assertOk()->assertJsonPath('data.0.user_id', $staff->id);

    Sanctum::actingAs($manager);
    $managerOtherUser = getJson("/api/v1/summaries/daily/{$otherStaff->id}");
    $managerOtherUser->assertForbidden();

    Sanctum::actingAs($admin);
    $adminPeriod = getJson('/api/v1/summaries/period?date_from=2026-03-01&date_to=2026-03-31');
    $adminPeriod->assertOk()
        ->assertJsonPath('total_logbooks', 2)
        ->assertJsonPath('target_angka_total', 20)
        ->assertJsonPath('capaian_angka_total', 10)
        ->assertJsonPath('progress_percent', 50);

    Sanctum::actingAs($staff);
    $kpiDaily = getJson('/api/v1/summaries/kpi/daily?tanggal=2026-03-19');
    $kpiDaily->assertOk()
        ->assertJsonPath('data.0.kpi_id', $kpi->id)
        ->assertJsonPath('data.0.total_lampiran', 1);

    Sanctum::actingAs($admin);
    $kpiPeriod = getJson('/api/v1/summaries/kpi/period?date_from=2026-03-01&date_to=2026-03-31');
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

    Sanctum::actingAs($manager);
    $filtered = getJson('/api/v1/summaries/daily?date_from=2026-03-15&date_to=2026-03-31');
    $filtered->assertOk()
        ->assertJsonCount(1, 'data');

    expect((string) $filtered->json('data.0.tanggal'))->toStartWith('2026-03-20');

    Sanctum::actingAs($manager);
    $period = getJson('/api/v1/summaries/period?date_from=2026-03-10&date_to=2026-03-10');
    $period->assertOk()
        ->assertJsonPath('target_angka_total', 0)
        ->assertJsonPath('capaian_angka_total', 0)
        ->assertJsonPath('progress_percent', 0);
});

it('team daily returns empty data for admin and forbidden for non-manager staff', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $staff = User::factory()->create(['role' => 'STAFF']);

    Sanctum::actingAs($admin);
    $adminResponse = getJson('/api/v1/summaries/team/daily');
    $adminResponse->assertOk()->assertJsonCount(0, 'data');

    Sanctum::actingAs($staff);
    getJson('/api/v1/summaries/team/daily')
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

it('staff-performance returns total_days_worked, total_work_hours, average_rating instead of raw KPI values', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $manager = User::factory()->create(['role' => 'STAFF']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    // Create daily summaries for 3 days with known work minutes
    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-10',
        'total_logbooks' => 1,
        'submitted_logbooks' => 0,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 480, // 8 hours
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 15,
        'progress_percent' => 75,
    ]);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-11',
        'total_logbooks' => 1,
        'submitted_logbooks' => 0,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 420, // 7 hours
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 18,
        'progress_percent' => 90,
    ]);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-12',
        'total_logbooks' => 1,
        'submitted_logbooks' => 1,
        'accepted_logbooks' => 0,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 300, // 5 hours
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 10,
        'progress_percent' => 50,
    ]);

    // Create accepted logbooks with ratings (withoutEvents to prevent observer from recalculating DailyStaffSummary)
    Logbook::withoutEvents(function () use ($staff) {
        Logbook::create([
            'user_id' => $staff->id,
            'tanggal' => '2026-03-10',
            'start_kerja' => '08:00:00',
            'end_kerja' => '16:00:00',
            'status' => 'ACCEPTED',
            'rating' => 4,
            'lokasi' => 'office',
        ]);

        Logbook::create([
            'user_id' => $staff->id,
            'tanggal' => '2026-03-11',
            'start_kerja' => '08:00:00',
            'end_kerja' => '15:00:00',
            'status' => 'ACCEPTED',
            'rating' => 5,
            'lokasi' => 'office',
        ]);

        // Non-rated logbook (SUBMITTED status) shouldn't count
        Logbook::create([
            'user_id' => $staff->id,
            'tanggal' => '2026-03-12',
            'start_kerja' => '08:00:00',
            'end_kerja' => '13:00:00',
            'status' => 'SUBMITTED',
            'rating' => null,
            'lokasi' => 'office',
        ]);
    });

    // Admin can view all staff
    Sanctum::actingAs($admin);
    $response = getJson('/api/v1/summaries/staff-performance?date_from=2026-03-10&date_to=2026-03-12');
    $response->assertOk();

    $items = $response->json('items');
    expect($items)->toHaveCount(1);

    $staffItem = $items[0];

    // Verify new fields exist
    expect($staffItem)->toHaveKeys([
        'user_id',
        'nama',
        'npp',
        'total_logbooks',
        'accepted_logbooks',
        'rejected_logbooks',
        'total_days_worked',
        'total_work_hours',
        'average_rating',
        'progress_percent',
    ]);

    // Verify old fields are removed
    expect($staffItem)->not->toHaveKey('target_angka_total');
    expect($staffItem)->not->toHaveKey('capaian_angka_total');

    // Verify calculated values
    expect($staffItem['total_days_worked'])->toBe(3);
    expect($staffItem['total_work_hours'])->toEqual(20); // (480 + 420 + 300) / 60 = 20 hours
    expect($staffItem['average_rating'])->toEqual(4.5); // (4 + 5) / 2 = 4.5
    expect($staffItem['progress_percent'])->toBeGreaterThan(0);
});

it('staff-performance returns null average_rating when no rated logbooks exist', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $staff = User::factory()->create(['role' => 'STAFF']);

    DailyStaffSummary::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-10',
        'total_logbooks' => 1,
        'submitted_logbooks' => 1,
        'accepted_logbooks' => 0,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 480,
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 15,
        'progress_percent' => 75,
    ]);

    // Submitted logbook without rating
    Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-10',
        'start_kerja' => '08:00:00',
        'end_kerja' => '16:00:00',
        'status' => 'SUBMITTED',
        'rating' => null,
        'lokasi' => 'office',
    ]);

    Sanctum::actingAs($admin);
    $response = getJson('/api/v1/summaries/staff-performance?date_from=2026-03-10&date_to=2026-03-10');
    $response->assertOk();

    $items = $response->json('items');
    expect($items[0]['average_rating'])->toBeNull();
    expect($items[0]['total_days_worked'])->toBe(1);
});

it('staff-performance is forbidden for non-manager staff', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    Sanctum::actingAs($staff);
    getJson('/api/v1/summaries/staff-performance')
        ->assertForbidden();
});

it('manager can only see their subordinates in staff-performance', function () {
    $manager = User::factory()->create(['role' => 'STAFF']);
    $subordinate = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $otherStaff = User::factory()->create(['role' => 'STAFF']);

    DailyStaffSummary::create([
        'user_id' => $subordinate->id,
        'tanggal' => '2026-03-10',
        'total_logbooks' => 1,
        'submitted_logbooks' => 0,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 480,
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 15,
        'progress_percent' => 75,
    ]);

    DailyStaffSummary::create([
        'user_id' => $otherStaff->id,
        'tanggal' => '2026-03-10',
        'total_logbooks' => 1,
        'submitted_logbooks' => 0,
        'accepted_logbooks' => 1,
        'rejected_logbooks' => 0,
        'total_work_minutes' => 480,
        'total_kpi' => 2,
        'target_angka_total' => 20,
        'capaian_angka_total' => 15,
        'progress_percent' => 75,
    ]);

    Sanctum::actingAs($manager);
    $response = getJson('/api/v1/summaries/staff-performance?date_from=2026-03-10&date_to=2026-03-10');
    $response->assertOk();

    $items = $response->json('items');
    expect($items)->toHaveCount(1);
    expect($items[0]['user_id'])->toBe($subordinate->id);
});

it('kpi-daily honors user_id filter and authorization for manager staff detail flow', function () {
    $manager = User::factory()->create(['role' => 'STAFF']);
    $subordinate = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);
    $otherStaff = User::factory()->create(['role' => 'STAFF']);

    $kpi = KpiMaster::factory()->create([
        'nama' => 'KPI Harian Detail',
        'target_angka' => 10,
        'satuan' => 'unit',
    ]);

    DailyKpiSummary::create([
        'user_id' => $subordinate->id,
        'kpi_id' => $kpi->id,
        'tanggal' => '2026-03-28',
        'kpi_nama' => 'KPI Harian Detail',
        'satuan' => 'unit',
        'target_angka_total' => 10,
        'capaian_angka_total' => 7,
        'progress_percent' => 70,
        'total_lampiran' => 1,
    ]);

    DailyKpiSummary::create([
        'user_id' => $otherStaff->id,
        'kpi_id' => $kpi->id,
        'tanggal' => '2026-03-28',
        'kpi_nama' => 'KPI Harian Detail',
        'satuan' => 'unit',
        'target_angka_total' => 10,
        'capaian_angka_total' => 9,
        'progress_percent' => 90,
        'total_lampiran' => 0,
    ]);

    Sanctum::actingAs($manager);
    $managerResponse = getJson("/api/v1/summaries/kpi/daily?user_id={$subordinate->id}&date_from=2026-03-28&date_to=2026-03-28");

    $managerResponse->assertOk();
    $managerResponse->assertJsonCount(1, 'data');
    expect($managerResponse->json('data.0.user_id'))->toBe($subordinate->id);

    Sanctum::actingAs($manager);
    $forbiddenResponse = getJson("/api/v1/summaries/kpi/daily?user_id={$otherStaff->id}&date_from=2026-03-28&date_to=2026-03-28");
    $forbiddenResponse->assertForbidden();
});
