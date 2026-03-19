<?php

namespace App\Observers;

use App\Models\Logbook;
use App\Services\DailySummaryService;

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
    }

    public function deleted(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }

    public function restored(Logbook $logbook): void
    {
        $this->dailySummaryService->syncForLogbook($logbook);
    }
}
