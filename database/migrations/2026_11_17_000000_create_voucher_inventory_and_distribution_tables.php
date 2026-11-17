<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table for Central Village Voucher Stocks (Drop Stok Sentral Desa)
        if (!Schema::hasTable('voucher_stocks')) {
            Schema::create('voucher_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
                $table->string('period', 7); // Format: YYYY-MM
                $table->string('voucher_type')->default('Voucher Rp 5.000');
                $table->string('allocation_type', 20)->default('initial'); // 'initial' (stok awal/lama) atau 'additional' (stok tambahan)
                $table->decimal('price_per_unit', 12, 2)->default(5000);
                $table->unsignedInteger('initial_stock')->default(0); // Misal 500 pcs
                $table->unsignedInteger('allocated_stock')->default(0); // Pcs yang sudah dibagikan ke penjual
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Table for Village Voucher Sellers / Resellers (Daftar Penjual per Desa)
        if (!Schema::hasTable('voucher_sellers')) {
            Schema::create('voucher_sellers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
                $table->string('name'); // Misal: Si A, Si B, Wiranto, Didi
                $table->string('phone', 25)->nullable();
                $table->string('address')->nullable();
                $table->decimal('default_fee_per_unit', 12, 2)->default(1000); // Default fee penjual per lembar
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Table for Stock Distribution to Sellers (Pembagian Stok ke Penjual)
        if (!Schema::hasTable('voucher_allocations')) {
            Schema::create('voucher_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
                $table->foreignId('voucher_seller_id')->constrained('voucher_sellers')->cascadeOnDelete();
                $table->foreignId('voucher_stock_id')->nullable()->constrained('voucher_stocks')->nullOnDelete();
                $table->string('period', 7); // Format: YYYY-MM
                $table->string('voucher_type')->default('Voucher Rp 5.000');
                $table->string('allocation_type', 20)->default('initial'); // 'initial' (stok awal/lama) atau 'additional' (stok tambahan)
                $table->decimal('price_per_unit', 12, 2)->default(5000);
                $table->unsignedInteger('quantity_given')->default(0); // Misal 150 pcs
                $table->unsignedInteger('quantity_sold')->default(0); // Terjual & kepotong
                $table->date('allocation_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Add relations to voucher_sales table if not present
        if (Schema::hasTable('voucher_sales')) {
            Schema::table('voucher_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_sales', 'voucher_seller_id')) {
                    $table->foreignId('voucher_seller_id')->nullable()->after('seller_phone')->constrained('voucher_sellers')->nullOnDelete();
                }
                if (!Schema::hasColumn('voucher_sales', 'voucher_allocation_id')) {
                    $table->foreignId('voucher_allocation_id')->nullable()->after('voucher_seller_id')->constrained('voucher_allocations')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('voucher_sales')) {
            Schema::table('voucher_sales', function (Blueprint $table) {
                if (Schema::hasColumn('voucher_sales', 'voucher_allocation_id')) {
                    $table->dropForeign(['voucher_allocation_id']);
                    $table->dropColumn('voucher_allocation_id');
                }
                if (Schema::hasColumn('voucher_sales', 'voucher_seller_id')) {
                    $table->dropForeign(['voucher_seller_id']);
                    $table->dropColumn('voucher_seller_id');
                }
            });
        }

        Schema::dropIfExists('voucher_allocations');
        Schema::dropIfExists('voucher_sellers');
        Schema::dropIfExists('voucher_stocks');
    }
};
