<?php

namespace App\Providers;

use App\Models\Grade;
use App\Observers\GradeObserver;
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
        // Register GradeObserver untuk auto-update grade_summary
        Grade::observe(GradeObserver::class);
    }
}
