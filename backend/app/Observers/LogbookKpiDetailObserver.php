<?php

namespace App\Observers;

use App\Models\LogbookKpiDetail;
use App\Services\DailySummaryService;

class LogbookKpiDetailObserver
{
    public function __construct(private readonly DailySummaryService $dailySummaryService) {}

    public function created(LogbookKpiDetail $detail): void
    {
        $this->dailySummaryService->syncForDetail($detail);
    }

    public function updated(LogbookKpiDetail $detail): void
    {
        $this->dailySummaryService->syncForDetail($detail);
    }

    public function deleted(LogbookKpiDetail $detail): void
    {
        $this->dailySummaryService->syncForDetail($detail);
    }

    public function restored(LogbookKpiDetail $detail): void
    {
        $this->dailySummaryService->syncForDetail($detail);
    }
}
