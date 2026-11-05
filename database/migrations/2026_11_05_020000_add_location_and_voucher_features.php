<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add coordinates to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('notes');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // 2. Add system_type to villages table ('pppoe', 'voucher', 'hybrid')
        Schema::table('villages', function (Blueprint $table) {
            $table->string('system_type', 20)->default('pppoe')->after('notes');
        });

        // 3. Create voucher_sales table for voucher tracking
        Schema::create('voucher_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('seller_name'); // Nama Penjual / Mitra / Warung
            $table->string('seller_phone', 25)->nullable();
            $table->string('period', 7); // Format: YYYY-MM
            $table->string('voucher_type'); // Contoh: Voucher 2 Jam, 24 Jam, 7 Hari
            $table->decimal('price_per_unit', 12, 2)->default(0); // Harga per lembar
            $table->unsignedInteger('quantity_sold')->default(0); // Jumlah lembar terjual
            $table->decimal('total_gross_amount', 12, 2)->default(0); // Total omset kotor
            $table->decimal('fee_per_unit', 12, 2)->default(0); // Fee per lembar
            $table->decimal('total_seller_fee', 12, 2)->default(0); // Total fee penjual
            $table->decimal('net_amount', 12, 2)->default(0); // Laba bersih Hokigo (Omset - Fee)
            $table->enum('status', ['deposited', 'pending'])->default('deposited'); // Sudah setor / belum
            $table->date('sale_date')->nullable();
            $table->timestamp('deposited_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_sales');

        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn('system_type');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
