<?php

namespace Tests\Feature;

use App\Models\DailyKpiSummary;
use App\Models\DailyStaffSummary;
use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use Database\Seeders\MigrateExistingDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('migration canonical columns and status behavior are coherent', function () {
    $staff = User::factory()->create([
        'nama' => 'Nama Pegawai',
        'npp' => '123456789012345678',
        'role' => 'STAFF',
    ]);

    expect($staff->nama)->toBe('Nama Pegawai');
    expect($staff->npp)->toBe('123456789012345678');
    expect($staff->role)->toBe('Staff');

    $kpi = KpiMaster::factory()->create(['target_angka' => 15, 'satuan' => 'dokumen']);
    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:00:00',
        'status' => 'DRAFT',
        'lokasi' => 'lokasi',
    ]);

    $detail = LogbookKpiDetail::create([
        'logbook_id' => $logbook->id,
        'kpi_id' => $kpi->id,
        'kpi_nama' => $kpi->nama,
        'target_angka' => 15,
        'satuan' => 'dokumen',
        'capaian_angka' => 0,
    ]);

    expect(Schema::hasColumn('logbook_kpi_details', 'is_finished'))->toBeFalse();
    expect($detail->finished_at)->toBeNull();
});

it('migrate existing data seeder produces coherent summary rows', function () {
    $manager = User::factory()->create(['role' => 'MANAGER']);
    $staff = User::factory()->create(['role' => 'STAFF', 'manager_id' => $manager->id]);

    $kpiA = KpiMaster::factory()->create(['nama' => 'KPI A', 'target_angka' => 10, 'satuan' => 'unit']);
    $kpiB = KpiMaster::factory()->create(['nama' => 'KPI B', 'target_angka' => 5, 'satuan' => 'jam']);

    $logbook = Logbook::create([
        'user_id' => $staff->id,
        'tanggal' => '2026-03-19',
        'start_kerja' => '08:00:00',
        'end_kerja' => '17:00:00',
        'status' => 'ACCEPTED',
        'lokasi' => 'lokasi',
    ]);

    LogbookKpiDetail::create([
        'logbook_id' => $logbook->id,
        'kpi_id' => $kpiA->id,
        'kpi_nama' => 'KPI A',
        'target_angka' => 10,
        'satuan' => 'unit',
        'capaian_angka' => 10,
        'lampiran_file' => 'logbook-kpi-attachments/a.pdf',
    ]);

    LogbookKpiDetail::create([
        'logbook_id' => $logbook->id,
        'kpi_id' => $kpiB->id,
        'kpi_nama' => 'KPI B',
        'target_angka' => 5,
        'satuan' => 'jam',
        'capaian_angka' => 3,
    ]);

    DailyStaffSummary::query()->delete();
    DailyKpiSummary::query()->delete();

    app(MigrateExistingDataSeeder::class)->run();

    $staffSummary = DailyStaffSummary::where('user_id', $staff->id)->whereDate('tanggal', '2026-03-19')->first();
    expect($staffSummary)->not->toBeNull();
    expect((int) $staffSummary->total_logbooks)->toBe(1);
    expect((int) $staffSummary->accepted_logbooks)->toBe(1);
    expect((int) $staffSummary->total_kpi)->toBe(2);
    expect((float) $staffSummary->target_angka_total)->toBe(15.0);
    expect((float) $staffSummary->capaian_angka_total)->toBe(13.0);
    expect((float) $staffSummary->progress_percent)->toBe(86.67);

    $kpiRows = DailyKpiSummary::where('user_id', $staff->id)->whereDate('tanggal', '2026-03-19')->orderBy('kpi_nama')->get();
    expect($kpiRows)->toHaveCount(2);
    expect((int) $kpiRows[0]->total_lampiran + (int) $kpiRows[1]->total_lampiran)->toBe(1);
});

it('database seeder and migration seeder create summary rows coherently', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--no-interaction' => true]);

    $staffSummariesCount = DB::table('daily_staff_summaries')->count();
    $kpiSummariesCount = DB::table('daily_kpi_summaries')->count();
    $logbooksCount = DB::table('logbooks')->count();

    expect($logbooksCount)->toBeGreaterThan(0);
    expect($staffSummariesCount)->toBeGreaterThan(0);
    expect($kpiSummariesCount)->toBeGreaterThan(0);

    $row = DB::table('daily_staff_summaries')->first();
    expect((int) $row->total_logbooks)->toBeGreaterThanOrEqual(1);
    expect((float) $row->target_angka_total)->toBeGreaterThanOrEqual(0);
    expect((float) $row->capaian_angka_total)->toBeGreaterThanOrEqual(0);
});
