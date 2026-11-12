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
        if (!Schema::hasTable('broadcast_logs')) {
            Schema::create('broadcast_logs', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('message');
                $table->string('target_type')->default('selected'); // 'all' or 'selected'
                $table->string('village_names')->nullable();
                $table->text('village_ids')->nullable();
                $table->string('customer_status')->default('active'); // 'active' or 'all'
                $table->integer('total_recipients')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_logs');
    }
};
