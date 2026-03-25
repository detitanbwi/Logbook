<?php

namespace App\Observers;

use App\Models\Logbook;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LogbookObserver
{
    public function __construct(private readonly DailySummaryService $dailySummaryService) {}

    public function created(Logbook $logbook): void
    {
        $this->safeSyncForLogbook($logbook);
    }

    public function updated(Logbook $logbook): void
    {
        $this->safeSyncForLogbook($logbook);

        // If date changed, also recalculate old date's summary
        if ($logbook->wasChanged('tanggal')) {
            $oldTanggal = $logbook->getOriginal('tanggal');
            if ($oldTanggal) {
                try {
                    $oldTanggalString = $oldTanggal instanceof Carbon
                        ? $oldTanggal->toDateString()
                        : Carbon::parse($oldTanggal)->toDateString();
                    $this->dailySummaryService->rebuildStaffSummary($logbook->user_id, $oldTanggalString);
                    $this->dailySummaryService->rebuildKpiSummaries($logbook->user_id, $oldTanggalString);
                } catch (\Throwable $e) {
                    Log::error('LogbookObserver: Failed to rebuild old date summary', [
                        'logbook_id' => $logbook->id,
                        'old_tanggal' => $oldTanggal,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    public function deleted(Logbook $logbook): void
    {
        $this->safeSyncForLogbook($logbook);
    }

    public function restored(Logbook $logbook): void
    {
        $this->safeSyncForLogbook($logbook);
    }

    public function forceDeleted(Logbook $logbook): void
    {
        $this->safeSyncForLogbook($logbook);
    }

    private function safeSyncForLogbook(Logbook $logbook): void
    {
        try {
            $this->dailySummaryService->syncForLogbook($logbook);
        } catch (\Throwable $e) {
            Log::error('LogbookObserver: Failed to sync summary for logbook', [
                'logbook_id' => $logbook->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
