<?php

namespace Tests\Feature;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
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
