<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('country_code')->constrained('countries')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->after('country_id')->constrained('zones')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->after('zone_id')->constrained('regions')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->after('region_id')->constrained('cities')->onDelete('set null');

            $table->index(['country_id', 'zone_id', 'region_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'zone_id', 'region_id', 'city_id']);
        });
    }
};
