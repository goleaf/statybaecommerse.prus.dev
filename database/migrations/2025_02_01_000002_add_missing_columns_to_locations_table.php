<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table): void {
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
    }

    public function down(): void
    {
        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table): void {
                foreach (['latitude', 'longitude', 'opening_hours', 'contact_info', 'sort_order'] as $col) {
                    if (Schema::hasColumn('locations', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
