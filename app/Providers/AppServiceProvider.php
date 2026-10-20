<?php

namespace App\Providers;

use App\Models\PurchaseRequest;
use App\Policies\PurchaseRequestPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        // AdminLTE 3 memakai Bootstrap 4 — pagination harus memakai view Bootstrap 4.
        Paginator::useBootstrapFour();

        // Policy RPO (PHASE 4).
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
    }
}
