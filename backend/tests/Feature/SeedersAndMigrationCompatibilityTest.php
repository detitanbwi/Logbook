<?php

namespace Tests\Feature;

use App\Models\KpiMaster;
use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use App\Models\UserKpiAssignment;
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
        'status' => 'SUBMITTED',
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

    $users = User::query()->orderBy('email')->get();
    expect($users)->toHaveCount(4);
    expect($users->pluck('role')->all())->toBe(['Admin', 'Staff', 'Staff', 'SuperAdmin']);
    expect(Schema::hasColumn('logbooks', 'lokasi_lat'))->toBeTrue();
    expect(Schema::hasColumn('logbooks', 'lokasi_lng'))->toBeTrue();

    $superAdmin = $users->firstWhere('role', 'SuperAdmin');
    $admin = $users->firstWhere('role', 'Admin');
    $staffLead = $users->firstWhere('email', 'staff.lead@logbook.com');
    $staffSubordinate = $users->firstWhere('email', 'staff.subordinate@logbook.com');

    expect($superAdmin)->not->toBeNull();
    expect($admin)->not->toBeNull();
    expect($staffLead)->not->toBeNull();
    expect($staffSubordinate)->not->toBeNull();

    expect($staffLead?->manager_id)->toBe($admin?->id);
    expect($staffSubordinate?->manager_id)->toBe($staffLead?->id);

    expect(UserKpiAssignment::query()->where('user_id', $staffLead?->id)->count())->toBe(3);
    expect(UserKpiAssignment::query()->where('user_id', $staffSubordinate?->id)->count())->toBe(3);

    $seededStatuses = Logbook::query()->distinct()->pluck('status')->sort()->values()->all();
    expect($seededStatuses)->toBe(['ACCEPTED', 'REJECTED', 'SUBMITTED']);
    expect(Logbook::query()->whereNotIn('status', ['SUBMITTED', 'ACCEPTED', 'REJECTED'])->count())->toBe(0);

    expect(Logbook::query()->where('user_id', $staffLead?->id)->count())->toBeGreaterThanOrEqual(8);
    expect(Logbook::query()->where('user_id', $staffSubordinate?->id)->count())->toBeGreaterThanOrEqual(6);

    $logbookWithCoordinates = Logbook::query()->whereNotNull('lokasi_lat')->whereNotNull('lokasi_lng')->first();
    expect($logbookWithCoordinates)->not->toBeNull();

    $historicalFinishedDetail = LogbookKpiDetail::query()
        ->whereNotNull('finished_at')
        ->whereHas('logbook', fn ($query) => $query->whereDate('tanggal', '<', now()->toDateString()))
        ->with('logbook:id,tanggal')
        ->first();

    expect($historicalFinishedDetail)->not->toBeNull();
    expect($historicalFinishedDetail?->logbook)->not->toBeNull();
    expect($historicalFinishedDetail?->finished_at?->toDateString())->toBe($historicalFinishedDetail?->logbook?->tanggal?->toDateString());
});
