<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sh_country_zone', function (Blueprint $table) {
            // Pivot table: no id, no timestamps
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('country_id');

            $table->unique(['zone_id', 'country_id'], 'country_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_country_zone');
    }
};
