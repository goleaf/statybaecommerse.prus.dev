<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sh_zones') && Schema::hasTable('sh_countries') && ! Schema::hasTable('sh_country_zone')) {
            Schema::create('sh_country_zone', function (Blueprint $table) {
                // Pivot table: no id, no timestamps
                $table->foreignId('zone_id')->constrained('sh_zones')->cascadeOnDelete();
                $table->foreignId('country_id')->constrained('sh_countries')->cascadeOnDelete();

                $table->unique(['zone_id', 'country_id'], 'country_zone_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_country_zone');
    }
};
