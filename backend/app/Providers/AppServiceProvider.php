<?php

namespace App\Providers;

use App\Models\Logbook;
use App\Models\LogbookKpiDetail;
use App\Observers\LogbookKpiDetailObserver;
use App\Observers\LogbookObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Logbook::observe(LogbookObserver::class);
        LogbookKpiDetail::observe(LogbookKpiDetailObserver::class);
    }
}
