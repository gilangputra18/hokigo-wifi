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
        // 1. Add center coordinates to villages table
        Schema::table('villages', function (Blueprint $table) {
            if (!Schema::hasColumn('villages', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('description');
            }
            if (!Schema::hasColumn('villages', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('villages', 'zoom_level')) {
                $table->integer('zoom_level')->default(16)->nullable()->after('longitude');
            }
        });

        // 2. Create network infrastructure nodes (Server, Starlink Dish, ODP, Switch, Tiang)
        if (!Schema::hasTable('network_nodes')) {
            Schema::create('network_nodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
                $table->string('name');
                $table->string('type')->default('odp'); // 'server', 'starlink', 'odp', 'switch', 'pole'
                $table->string('capacity')->nullable(); // e.g. "8 Port", "16 Port", "Standard Starlink"
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->text('notes')->nullable();
                $table->string('status')->default('active'); // 'active', 'maintenance'
                $table->timestamps();
            });
        }

        // 3. Create cable routes table (Dropcore, Distribution, Backbone FO)
        if (!Schema::hasTable('cable_routes')) {
            Schema::create('cable_routes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
                $table->string('name');
                $table->string('cable_type')->default('dropcore'); // 'dropcore', 'distribution', 'backbone'
                $table->string('color')->default('#10b981'); // Hex color code
                $table->string('core_count')->nullable(); // e.g. "1 Core", "2 Core", "24 Core"
                $table->json('coordinates'); // Array of [[lat, lng], [lat, lng], ...]
                $table->integer('length_meters')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('active'); // 'active', 'damaged', 'maintenance'
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_routes');
        Schema::dropIfExists('network_nodes');

        Schema::table('villages', function (Blueprint $table) {
            if (Schema::hasColumn('villages', 'latitude')) {
                $table->dropColumn(['latitude', 'longitude', 'zoom_level']);
            }
        });
    }
};
