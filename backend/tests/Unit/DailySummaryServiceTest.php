<?php

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use App\Services\DailySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(DailySummaryService::class);
});

describe('rebuildStaffSummary', function () {
    test('creates staff summary for single logbook with kpi details', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create(['target_angka' => 100, 'satuan' => 'unit']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => '17:00:00',
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'kpi_nama' => $kpi->nama,
            'target_angka' => 100,
            'capaian_angka' => 50,
            'satuan' => 'unit',
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->not->toBeNull()
            ->and((int) $summary->total_logbooks)->toBe(1)
            ->and((int) $summary->submitted_logbooks)->toBe(0)
            ->and((int) $summary->accepted_logbooks)->toBe(0)
            ->and((int) $summary->rejected_logbooks)->toBe(0)
            ->and((int) $summary->total_kpi)->toBe(1)
            ->and((float) $summary->target_angka_total)->toBe(100.0)
            ->and((float) $summary->capaian_angka_total)->toBe(50.0)
            ->and((float) $summary->progress_percent)->toBe(50.0);
    });

    test('calculates correct work minutes with break deduction', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => '17:00:00', // 9 hours gross, 8 hours net (1 hour break)
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'target_angka' => 10,
            'capaian_angka' => 5,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // 9 hours = 540 minutes gross, minus 60 minutes break = 480 minutes net
        expect((int) $summary->total_work_minutes)->toBe(480);
    });

    test('handles work time not overlapping with break period', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '07:00:00',
            'end_kerja' => '11:00:00', // 4 hours, no break overlap
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'target_angka' => 10,
            'capaian_angka' => 5,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // 4 hours = 240 minutes, no break overlap
        expect((int) $summary->total_work_minutes)->toBe(240);
    });

    test('handles partial break overlap', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '10:00:00',
            'end_kerja' => '12:30:00', // 2.5 hours, 30 min break overlap
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'target_angka' => 10,
            'capaian_angka' => 5,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // 2.5 hours = 150 minutes gross, minus 30 minutes break = 120 minutes net
        expect((int) $summary->total_work_minutes)->toBe(120);
    });

    test('aggregates multiple logbooks on same day', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        // First logbook: SUBMITTED
        $logbook1 = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => '12:00:00',
            'status' => 'SUBMITTED',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook1->id,
            'target_angka' => 100,
            'capaian_angka' => 50,
        ]);

        // Second logbook: ACCEPTED
        $logbook2 = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '13:00:00',
            'end_kerja' => '17:00:00',
            'status' => 'ACCEPTED',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook2->id,
            'target_angka' => 50,
            'capaian_angka' => 50,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->not->toBeNull()
            ->and((int) $summary->total_logbooks)->toBe(2)
            ->and((int) $summary->submitted_logbooks)->toBe(1)
            ->and((int) $summary->accepted_logbooks)->toBe(1)
            ->and((int) $summary->rejected_logbooks)->toBe(0)
            ->and((int) $summary->total_kpi)->toBe(2)
            ->and((float) $summary->target_angka_total)->toBe(150.0)
            ->and((float) $summary->capaian_angka_total)->toBe(100.0);
    });

    test('deletes summary when no logbooks exist for date', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        // Create a summary first
        DailyStaffSummary::create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'total_logbooks' => 1,
            'submitted_logbooks' => 0,
            'accepted_logbooks' => 0,
            'rejected_logbooks' => 0,
            'total_work_minutes' => 60,
            'total_kpi' => 1,
            'target_angka_total' => 100,
            'capaian_angka_total' => 50,
            'progress_percent' => 50,
        ]);

        // Rebuild with no logbooks
        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->toBeNull();
    });

    test('handles zero target correctly (avoids division by zero)', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => '17:00:00',
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'target_angka' => 0,
            'capaian_angka' => 0,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->not->toBeNull()
            ->and((float) $summary->progress_percent)->toBe(0.0);
    });

    test('handles null end_kerja (work in progress)', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => null, // Work in progress
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'target_angka' => 100,
            'capaian_angka' => 50,
        ]);

        $this->service->rebuildStaffSummary($staff->id, $tanggal);

        $summary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->not->toBeNull()
            ->and((int) $summary->total_work_minutes)->toBe(0);
    });
});

describe('rebuildKpiSummaries', function () {
    test('creates kpi summary for single kpi on single logbook', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create(['nama' => 'Test KPI', 'target_angka' => 100, 'satuan' => 'unit']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'kpi_nama' => 'Test KPI',
            'target_angka' => 100,
            'capaian_angka' => 75,
            'satuan' => 'unit',
            'lampiran_file' => 'test.pdf',
        ]);

        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $kpiSummary = DailyKpiSummary::where('user_id', $staff->id)
            ->where('kpi_id', $kpi->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($kpiSummary)->not->toBeNull()
            ->and($kpiSummary->kpi_nama)->toBe('Test KPI')
            ->and((float) $kpiSummary->target_angka_total)->toBe(100.0)
            ->and((float) $kpiSummary->capaian_angka_total)->toBe(75.0)
            ->and((float) $kpiSummary->progress_percent)->toBe(75.0)
            ->and((int) $kpiSummary->total_lampiran)->toBe(1);
    });

    test('aggregates same kpi across multiple logbooks', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create(['nama' => 'Shared KPI', 'target_angka' => 50, 'satuan' => 'unit']);
        $tanggal = '2025-01-15';

        $logbook1 = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook1->id,
            'kpi_id' => $kpi->id,
            'kpi_nama' => 'Shared KPI',
            'target_angka' => 50,
            'capaian_angka' => 25,
            'satuan' => 'unit',
            'lampiran_file' => 'file1.pdf',
        ]);

        $logbook2 = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'SUBMITTED',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook2->id,
            'kpi_id' => $kpi->id,
            'kpi_nama' => 'Shared KPI',
            'target_angka' => 50,
            'capaian_angka' => 30,
            'satuan' => 'unit',
            'lampiran_file' => 'file2.pdf',
        ]);

        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $kpiSummary = DailyKpiSummary::where('user_id', $staff->id)
            ->where('kpi_id', $kpi->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($kpiSummary)->not->toBeNull()
            ->and((float) $kpiSummary->target_angka_total)->toBe(100.0) // 50 + 50
            ->and((float) $kpiSummary->capaian_angka_total)->toBe(55.0) // 25 + 30
            ->and((int) $kpiSummary->total_lampiran)->toBe(2);
    });

    test('creates separate summaries for different kpis', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi1 = KpiMaster::factory()->create(['nama' => 'KPI One']);
        $kpi2 = KpiMaster::factory()->create(['nama' => 'KPI Two']);
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi1->id,
            'kpi_nama' => 'KPI One',
            'target_angka' => 100,
            'capaian_angka' => 80,
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi2->id,
            'kpi_nama' => 'KPI Two',
            'target_angka' => 50,
            'capaian_angka' => 25,
        ]);

        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $summaries = DailyKpiSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->get();

        expect($summaries)->toHaveCount(2);

        $kpi1Summary = $summaries->firstWhere('kpi_id', $kpi1->id);
        $kpi2Summary = $summaries->firstWhere('kpi_id', $kpi2->id);

        expect((float) $kpi1Summary->capaian_angka_total)->toBe(80.0)
            ->and((float) $kpi2Summary->capaian_angka_total)->toBe(25.0);
    });

    test('deletes kpi summaries when no details exist', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create();
        $tanggal = '2025-01-15';

        // Create a summary first
        DailyKpiSummary::create([
            'user_id' => $staff->id,
            'kpi_id' => $kpi->id,
            'tanggal' => $tanggal,
            'kpi_nama' => 'Old KPI',
            'satuan' => 'unit',
            'target_angka_total' => 100,
            'capaian_angka_total' => 50,
            'progress_percent' => 50,
            'total_lampiran' => 0,
        ]);

        // Rebuild with no logbooks/details
        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $summary = DailyKpiSummary::where('user_id', $staff->id)
            ->where('kpi_id', $kpi->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($summary)->toBeNull();
    });

    test('counts only non-null lampiran files', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create();
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        // Detail with attachment
        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'lampiran_file' => 'attached.pdf',
        ]);

        // Detail without attachment
        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'lampiran_file' => null,
        ]);

        // Detail with empty string (should not count)
        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'lampiran_file' => '',
        ]);

        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $kpiSummary = DailyKpiSummary::where('user_id', $staff->id)
            ->where('kpi_id', $kpi->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Only the non-empty lampiran should be counted
        expect((int) $kpiSummary->total_lampiran)->toBe(1);
    });

    test('removes orphan kpi summaries after kpi removal', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi1 = KpiMaster::factory()->create(['nama' => 'Keep KPI']);
        $kpi2 = KpiMaster::factory()->create(['nama' => 'Remove KPI']);
        $tanggal = '2025-01-15';

        // Create summaries for both KPIs
        DailyKpiSummary::create([
            'user_id' => $staff->id,
            'kpi_id' => $kpi1->id,
            'tanggal' => $tanggal,
            'kpi_nama' => 'Keep KPI',
            'satuan' => 'unit',
            'target_angka_total' => 100,
            'capaian_angka_total' => 50,
            'progress_percent' => 50,
            'total_lampiran' => 0,
        ]);

        DailyKpiSummary::create([
            'user_id' => $staff->id,
            'kpi_id' => $kpi2->id,
            'tanggal' => $tanggal,
            'kpi_nama' => 'Remove KPI',
            'satuan' => 'unit',
            'target_angka_total' => 100,
            'capaian_angka_total' => 50,
            'progress_percent' => 50,
            'total_lampiran' => 0,
        ]);

        // Create logbook with only KPI1
        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi1->id,
            'kpi_nama' => 'Keep KPI',
        ]);

        // Rebuild - should remove KPI2 summary
        $this->service->rebuildKpiSummaries($staff->id, $tanggal);

        $remainingSummaries = DailyKpiSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->get();

        expect($remainingSummaries)->toHaveCount(1)
            ->and($remainingSummaries->first()->kpi_id)->toBe($kpi1->id);
    });
});

describe('syncForLogbook', function () {
    test('syncs both staff and kpi summaries', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create();
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'start_kerja' => '08:00:00',
            'end_kerja' => '17:00:00',
            'status' => 'DRAFT',
        ]);

        LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'target_angka' => 100,
            'capaian_angka' => 50,
        ]);

        $this->service->syncForLogbook($logbook);

        $staffSummary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $kpiSummary = DailyKpiSummary::where('user_id', $staff->id)
            ->where('kpi_id', $kpi->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($staffSummary)->not->toBeNull()
            ->and($kpiSummary)->not->toBeNull();
    });

    test('skips sync when tanggal is null', function () {
        $staff = User::factory()->create(['role' => 'Staff']);

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => null,
            'status' => 'DRAFT',
        ]);

        // Should not throw an error
        $this->service->syncForLogbook($logbook);

        $summaryCount = DailyStaffSummary::where('user_id', $staff->id)->count();
        expect($summaryCount)->toBe(0);
    });
});

describe('syncForDetail', function () {
    test('syncs summaries for detail\'s logbook', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create();
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        $detail = LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
            'target_angka' => 100,
            'capaian_angka' => 75,
        ]);

        $this->service->syncForDetail($detail);

        $staffSummary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($staffSummary)->not->toBeNull()
            ->and((float) $staffSummary->capaian_angka_total)->toBe(75.0);
    });

    test('handles soft-deleted logbook', function () {
        $staff = User::factory()->create(['role' => 'Staff']);
        $kpi = KpiMaster::factory()->create();
        $tanggal = '2025-01-15';

        $logbook = Logbook::factory()->create([
            'user_id' => $staff->id,
            'tanggal' => $tanggal,
            'status' => 'DRAFT',
        ]);

        $detail = LogbookKpiDetail::factory()->create([
            'logbook_id' => $logbook->id,
            'kpi_id' => $kpi->id,
        ]);

        // Soft delete the logbook
        $logbook->delete();

        // Should still be able to sync (uses withTrashed)
        $this->service->syncForDetail($detail);

        // Summary should be deleted since logbook is soft-deleted and not included in query
        $staffSummary = DailyStaffSummary::where('user_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        expect($staffSummary)->toBeNull();
    });
});
