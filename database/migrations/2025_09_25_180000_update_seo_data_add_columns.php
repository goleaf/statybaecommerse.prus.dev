<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_data')) {
            return;
        }

        Schema::table('seo_data', function (Blueprint $table): void {
            if (! Schema::hasColumn('seo_data', 'type')) {
                $table->string('type')->nullable()->after('locale');
            }
            if (! Schema::hasColumn('seo_data', 'url')) {
                $table->string('url')->nullable()->after('type');
            }
            if (! Schema::hasColumn('seo_data', 'is_indexed')) {
                $table->boolean('is_indexed')->default(true)->after('url');
            }
            if (! Schema::hasColumn('seo_data', 'is_canonical')) {
                $table->boolean('is_canonical')->default(false)->after('is_indexed');
            }
            if (! Schema::hasColumn('seo_data', 'deleted_at')) {
                $table->softDeletes();
            }

            // Relax morph columns to allow null when detached
            if (Schema::hasColumn('seo_data', 'seoable_type')) {
                try {
                    $table->string('seoable_type')->nullable()->change();
                } catch (\Throwable $e) {
                }
            }
            if (Schema::hasColumn('seo_data', 'seoable_id')) {
                try {
                    $table->unsignedBigInteger('seoable_id')->nullable()->change();
                } catch (\Throwable $e) {
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('seo_data')) {
            return;
        }

        Schema::table('seo_data', function (Blueprint $table): void {
            if (Schema::hasColumn('seo_data', 'deleted_at')) {
                try {
                    $table->dropSoftDeletes();
                } catch (\Throwable $e) {
                }
            }
            foreach (['type', 'url', 'is_indexed', 'is_canonical'] as $col) {
                if (Schema::hasColumn('seo_data', $col)) {
                    try {
                        $table->dropColumn($col);
                    } catch (\Throwable $e) {
                    }
                }
            }
            // Re-tighten morph columns is skipped to avoid data loss
        });
    }
};

