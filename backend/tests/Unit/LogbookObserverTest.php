<?php

use App\Models\Logbook;
use App\Models\User;
use App\Observers\LogbookObserver;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('LogbookObserver', function () {
    describe('created', function () {
        test('calls syncForLogbook when logbook is created', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once()
                ->withArgs(fn (Logbook $logbook) => true);

            $observer = new LogbookObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->make([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);
            $logbook->saveQuietly();

            $observer->created($logbook);
        });
    });

    describe('updated', function () {
        test('calls syncForLogbook when logbook is updated', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once()
                ->withArgs(fn (Logbook $logbook) => true);

            $observer = new LogbookObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
                'status' => 'DRAFT',
            ]);

            // Simulate the state after save() but before observer resets changes
            $logbook->status = 'SUBMITTED';
            $logbook->syncChanges();

            $observer->updated($logbook);
        });

        test('recalculates old date when tanggal changes', function () {
            $staff = User::factory()->create(['role' => 'Staff']);
            $oldDate = '2025-01-10';
            $newDate = '2025-01-15';

            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => $oldDate,
            ]);

            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once()
                ->withArgs(fn (Logbook $lb) => $lb->id === $logbook->id);
            $mockService->shouldReceive('rebuildStaffSummary')
                ->once()
                ->with($staff->id, $oldDate);
            $mockService->shouldReceive('rebuildKpiSummaries')
                ->once()
                ->with($staff->id, $oldDate);

            $observer = new LogbookObserver($mockService);

            // Change tanggal and sync changes to simulate post-save state
            $logbook->tanggal = $newDate;
            $logbook->syncChanges();

            $observer->updated($logbook);
        });

        test('handles Carbon instance for old tanggal when date changes', function () {
            $staff = User::factory()->create(['role' => 'Staff']);
            $oldDate = Carbon::parse('2025-01-10');
            $newDate = '2025-01-15';

            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => $oldDate,
            ]);

            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once();
            $mockService->shouldReceive('rebuildStaffSummary')
                ->once()
                ->with($staff->id, '2025-01-10');
            $mockService->shouldReceive('rebuildKpiSummaries')
                ->once()
                ->with($staff->id, '2025-01-10');

            $observer = new LogbookObserver($mockService);

            // Change tanggal and sync changes to simulate post-save state
            $logbook->tanggal = $newDate;
            $logbook->syncChanges();

            $observer->updated($logbook);
        });

        test('does not recalculate old date when tanggal does not change', function () {
            $staff = User::factory()->create(['role' => 'Staff']);

            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
                'status' => 'DRAFT',
            ]);

            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once();
            $mockService->shouldNotReceive('rebuildStaffSummary');
            $mockService->shouldNotReceive('rebuildKpiSummaries');

            $observer = new LogbookObserver($mockService);

            // Only change status, not tanggal
            $logbook->status = 'SUBMITTED';
            $logbook->syncChanges();

            $observer->updated($logbook);
        });
    });

    describe('deleted', function () {
        test('calls syncForLogbook when logbook is deleted', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once()
                ->withArgs(fn (Logbook $logbook) => true);

            $observer = new LogbookObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $logbook->deleteQuietly();

            $observer->deleted($logbook);
        });
    });

    describe('restored', function () {
        test('calls syncForLogbook when logbook is restored', function () {
            $mockService = Mockery::mock(DailySummaryService::class);
            $mockService->shouldReceive('syncForLogbook')
                ->once()
                ->withArgs(fn (Logbook $logbook) => true);

            $observer = new LogbookObserver($mockService);
            $staff = User::factory()->create(['role' => 'Staff']);
            $logbook = Logbook::factory()->create([
                'user_id' => $staff->id,
                'tanggal' => '2025-01-15',
            ]);

            $logbook->deleteQuietly();
            $logbook->restoreQuietly();

            $observer->restored($logbook);
        });
    });
});
