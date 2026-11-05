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
                    $nowMonth = now()->format('Y-m');
                    $today = now()->toDateString();
                    $invRow = Invoice::whereIn('status', ['unpaid', 'overdue'])
                        ->selectRaw("COUNT(CASE WHEN period = ? THEN 1 END) as unpaid_month, COUNT(CASE WHEN due_date <= ? THEN 1 END) as due_count", [$nowMonth, $today])
                        ->first();

                    $counts = [
                        'total_customers' => Customer::count(),
                        'unpaid_month' => (int) ($invRow->unpaid_month ?? 0),
                        'due_count' => (int) ($invRow->due_count ?? 0),
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
