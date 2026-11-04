<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('name');
            $table->string('phone', 25);
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->text('address')->nullable();
            $table->decimal('monthly_fee', 12, 2)->default(0);
            $table->unsignedTinyInteger('due_date_day')->default(10); // Day of month (1-28/31)
            $table->date('installation_date')->nullable();
            $table->string('router_model')->nullable();
            $table->string('pppoe_user')->nullable();
            $table->string('pppoe_password')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('status', ['active', 'isolated', 'inactive'])->default('active');
            $table->text('notes')->nullable(); // Catatan khusus per pelanggan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
