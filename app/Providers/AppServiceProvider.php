<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

        // Fast In-Memory View Composer for Sidebar Badges (Prevents redundant DB queries on every page)
        View::composer('layouts.app', function ($view) {
            static $counts = null;
            if ($counts === null) {
                try {
                    $counts = [
                        'total_customers' => Customer::count(),
                        'unpaid_month' => Invoice::where('period', now()->format('Y-m'))->whereIn('status', ['unpaid', 'overdue'])->count(),
                        'due_count' => Invoice::whereIn('status', ['unpaid', 'overdue'])->whereDate('due_date', '<=', now()->toDateString())->count(),
                    ];
                } catch (\Throwable $e) {
                    $counts = [
                        'total_customers' => 0,
                        'unpaid_month' => 0,
                        'due_count' => 0,
                    ];
                }
            }
            $view->with('sidebarCounts', $counts);
        });
    }
}
