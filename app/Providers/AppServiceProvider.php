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

        // Auto-ensure plain_password column exists on users table (Serverless PostgreSQL Supabase)
        try {
            static $plainPassChecked = false;
            if (!$plainPassChecked) {
                $plainPassChecked = true;
                if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'plain_password')) {
                    \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('plain_password')->nullable()->after('password');
                    });
                }
            }
        } catch (\Throwable $e) {}

        // Auto-ensure broadcast_logs table exists (Serverless PostgreSQL Supabase)
        try {
            static $broadcastTableChecked = false;
            if (!$broadcastTableChecked) {
                $broadcastTableChecked = true;
                if (!\Illuminate\Support\Facades\Schema::hasTable('broadcast_logs')) {
                    \Illuminate\Support\Facades\Schema::create('broadcast_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->id();
                        $table->string('title')->nullable();
                        $table->text('message');
                        $table->string('target_type')->default('selected');
                        $table->string('village_names')->nullable();
                        $table->text('village_ids')->nullable();
                        $table->string('customer_status')->default('active');
                        $table->integer('total_recipients')->default(0);
                        $table->integer('sent_count')->default(0);
                        $table->integer('failed_count')->default(0);
                        $table->unsignedBigInteger('created_by')->nullable();
                        $table->timestamps();
                    });
                }
            }
        } catch (\Throwable $e) {}

        // Fast In-Memory & Session-Cached View Composer for Sidebar Badges (Eliminates DB queries on page navigation)
        View::composer('layouts.app', function ($view) {
            static $counts = null;
            if ($counts !== null) {
                $view->with('sidebarCounts', $counts);
                return;
            }

            try {
                $cached = session('sidebar_counts');
                $cachedTime = (int) session('sidebar_counts_time', 0);
                if (is_array($cached) && (time() - $cachedTime) < 60) {
                    $counts = $cached;
                    $view->with('sidebarCounts', $counts);
                    return;
                }
            } catch (\Throwable $e) {
                // Ignore if session not active
            }

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

                try {
                    session([
                        'sidebar_counts' => $counts,
                        'sidebar_counts_time' => time(),
                    ]);
                } catch (\Throwable $e) {
                    // Ignore session write failure
                }
            } catch (\Throwable $e) {
                $counts = [
                    'total_customers' => 0,
                    'unpaid_month' => 0,
                    'due_count' => 0,
                ];
            }

            $view->with('sidebarCounts', $counts);
        });
    }
}
