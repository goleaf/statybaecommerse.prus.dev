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
        if (! Schema::hasTable('country_zone')) {
            return;
        }

        $driver = DB::getDriverName();
        $disableFk = $driver === 'sqlite' ? 'PRAGMA foreign_keys=OFF' : 'SET FOREIGN_KEY_CHECKS=0';
        $enableFk = $driver === 'sqlite' ? 'PRAGMA foreign_keys=ON' : 'SET FOREIGN_KEY_CHECKS=1';

        DB::statement($disableFk);

        try {
            Schema::dropIfExists('tmp_country_zone');

            Schema::create('tmp_country_zone', function (Blueprint $table) {
                $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
                $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                $table->unique(['zone_id', 'country_id'], 'tmp_country_zone_unique');
            });

            DB::table('country_zone')->orderBy('zone_id')->orderBy('country_id')->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('tmp_country_zone')->insert([
                        'zone_id' => $row->zone_id,
                        'country_id' => $row->country_id,
                    ]);
                }
            });

            Schema::dropIfExists('country_zone');
            Schema::rename('tmp_country_zone', 'country_zone');
        } finally {
            DB::statement($enableFk);
        }
    }

    public function down(): void
    {
        // No-op: keep corrected FKs
    }
};
