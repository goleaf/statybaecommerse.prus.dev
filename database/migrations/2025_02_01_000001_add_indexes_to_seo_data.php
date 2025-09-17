<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('seo_data')) {
            Schema::table('seo_data', function (Blueprint $table) {
                if (!Schema::hasColumn('seo_data', 'created_at') && !Schema::hasColumn('seo_data', 'updated_at')) {
                    $table->timestamps();
                }
                try {
                    $table->index(['locale'], 'seo_data_locale_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seo_data')) {
            Schema::table('seo_data', function (Blueprint $table) {
                try {
                    $table->dropIndex('seo_data_locale_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropTimestamps();
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
