<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('house_number', 100)->nullable()->after('address'); // No Rumah / Gang
            $table->string('redaman', 30)->nullable()->after('router_model'); // Nilai Redaman (dBm), misal -18.5 dBm
            $table->text('house_photo')->nullable()->after('notes'); // Foto Rumah Pelanggan (Base64 / URL)
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['house_number', 'redaman', 'house_photo']);
        });
    }
};
