<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'wifi_ssid')) {
                $table->string('wifi_ssid')->nullable()->after('router_model');
            }
            if (!Schema::hasColumn('customers', 'wifi_password')) {
                $table->string('wifi_password')->nullable()->after('wifi_ssid');
            }
        });

        // Hapus semua data dummy / contoh agar sistem bersih di-nol-kan
        try {
            DB::table('invoices')->delete();
            DB::table('customers')->delete();
            if (Schema::hasTable('voucher_sales')) {
                DB::table('voucher_sales')->delete();
            }
            if (Schema::hasTable('financial_transactions')) {
                DB::table('financial_transactions')->delete();
            }
            if (Schema::hasTable('cable_routes')) {
                DB::table('cable_routes')->delete();
            }
            if (Schema::hasTable('network_nodes')) {
                DB::table('network_nodes')->delete();
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'wifi_password')) {
                $table->dropColumn('wifi_password');
            }
            if (Schema::hasColumn('customers', 'wifi_ssid')) {
                $table->dropColumn('wifi_ssid');
            }
        });
    }
};
