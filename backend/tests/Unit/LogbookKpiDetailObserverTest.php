<?php

use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Models\User;
use App\Observers\LogbookKpiDetailObserver;
use App\Services\DailySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('LogbookKpiDetailObserver', function () {
    describe('created', function () {
        test('calls syncForDetail when detail is created', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForDetail')
                ->once()
                ->withArgs(fn (LogbookKpiDetail $detail) => true);

            $observer = new LogbookKpiDetailObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $detail = LogbookKpiDetail::factory()->create([
                'logbook_id' => $logbook->id,
                'target_angka' => 100,
                'capaian_angka' => 50,
            ]);

            $observer->created($detail);
        });
    });

    describe('updated', function () {
        test('calls syncForDetail when detail is updated', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForDetail')
                ->once()
                ->withArgs(fn (LogbookKpiDetail $detail) => true);

            $observer = new LogbookKpiDetailObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $detail = LogbookKpiDetail::factory()->create([
                'logbook_id' => $logbook->id,
                'target_angka' => 100,
                'capaian_angka' => 50,
            ]);

            $detail->capaian_angka = 75;
            $detail->save();

            $observer->updated($detail);
        });

        test('calls syncForDetail when target_angka changes', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForDetail')
                ->once();

            $observer = new LogbookKpiDetailObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $detail = LogbookKpiDetail::factory()->create([
                'logbook_id' => $logbook->id,
                'target_angka' => 100,
                'capaian_angka' => 50,
            ]);

            $detail->target_angka = 200;
            $detail->save();

            $observer->updated($detail);
        });
    });

    describe('deleted', function () {
        test('calls syncForDetail when detail is deleted', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForDetail')
                ->once()
                ->withArgs(fn (LogbookKpiDetail $detail) => true);

            $observer = new LogbookKpiDetailObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $detail = LogbookKpiDetail::factory()->create([
                'logbook_id' => $logbook->id,
                'target_angka' => 100,
                'capaian_angka' => 50,
            ]);

            $detail->delete();

            $observer->deleted($detail);
        });
    });

    describe('restored', function () {
        test('calls syncForDetail when detail is restored', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForDetail')
                ->once()
                ->withArgs(fn (LogbookKpiDetail $detail) => true);

            $observer = new LogbookKpiDetailObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $detail = LogbookKpiDetail::factory()->create([
                'logbook_id' => $logbook->id,
                'target_angka' => 100,
                'capaian_angka' => 50,
            ]);

            $detail->delete();
            $detail->restore();

            $observer->restored($detail);
        });
    });
});
