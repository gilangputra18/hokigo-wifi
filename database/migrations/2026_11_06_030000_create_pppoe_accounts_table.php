<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pppoe_accounts')) {
            Schema::create('pppoe_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->nullable()->constrained('villages')->onDelete('cascade');
                $table->string('username')->index();
                $table->string('password')->default('Irzaq09042003');
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->unique(['village_id', 'username']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pppoe_accounts');
    }
};
