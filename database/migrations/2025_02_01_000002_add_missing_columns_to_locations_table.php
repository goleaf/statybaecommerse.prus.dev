<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('locations', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('locations', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('locations', 'opening_hours')) {
                $table->json('opening_hours')->nullable();
            }
            if (!Schema::hasColumn('locations', 'contact_info')) {
                $table->json('contact_info')->nullable();
            }
            if (!Schema::hasColumn('locations', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'opening_hours', 'contact_info', 'sort_order']);
        });
    }
};
