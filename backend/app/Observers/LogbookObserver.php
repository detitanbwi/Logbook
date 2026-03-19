<?php

namespace App\Observers;

use App\Models\Logbook;
use App\Services\DailySummaryService;
use Carbon\Carbon;

class LogbookObserver
{
    public function __construct(private readonly DailySummaryService $dailySummaryService) {}

    public function created(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }

    public function updated(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);

        // If date changed, also recalculate old date's summary
        if ($logbook->wasChanged('tanggal')) {
            $oldTanggal = $logbook->getOriginal('tanggal');
            if ($oldTanggal) {
                // Convert to string format if it's a Carbon instance
                $oldTanggalString = $oldTanggal instanceof Carbon
                    ? $oldTanggal->toDateString()
                    : Carbon::parse($oldTanggal)->toDateString();
                $this->dailySummaryService->rebuildStaffSummary($logbook->user_id, $oldTanggalString);
                $this->dailySummaryService->rebuildKpiSummaries($logbook->user_id, $oldTanggalString);
            }
        }
    }

    public function deleted(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }

    public function restored(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }

    public function forceDeleted(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }
}
