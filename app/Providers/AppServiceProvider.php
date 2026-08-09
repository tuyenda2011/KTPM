<?php

namespace App\Providers;

use App\Models\JobPosting;
use App\Observers\JobPostingObserver;
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
        JobPosting::observe(JobPostingObserver::class);
    }
}
