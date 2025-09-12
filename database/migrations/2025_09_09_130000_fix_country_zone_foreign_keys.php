<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('country_zone')) {
            // Recreate table to fix foreign keys after renames from sh_* tables
            DB::statement('PRAGMA foreign_keys=OFF');
            try {
                Schema::dropIfExists('tmp_country_zone');

                Schema::create('tmp_country_zone', function (Blueprint $table) {
                    $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
                    $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                    $table->unique(['zone_id', 'country_id'], 'tmp_country_zone_unique');
                });

                // If old table existed, migrate data (best-effort)
                if (Schema::hasTable('country_zone')) {
                    DB::insert('insert into tmp_country_zone (zone_id, country_id) select zone_id, country_id from country_zone');
                }

                Schema::dropIfExists('country_zone');
                Schema::rename('tmp_country_zone', 'country_zone');
            } finally {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }
    }

    public function down(): void
    {
        // No-op: keep corrected FKs
    }
};
