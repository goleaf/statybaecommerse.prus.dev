<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('variant_images')) {
            return;
        }

        Schema::table('variant_images', function (Blueprint $table): void {
            if (! Schema::hasColumn('variant_images', 'description')) {
                $table->text('description')->nullable()->after('alt_text');
            }

            if (! Schema::hasColumn('variant_images', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_primary');
                $table->index('is_active', 'variant_images_is_active_index');
            }

            if (! Schema::hasColumn('variant_images', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('variant_images', 'dimensions')) {
                $table->string('dimensions')->nullable()->after('file_size');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('variant_images')) {
            return;
        }

        Schema::table('variant_images', function (Blueprint $table): void {
            if (Schema::hasColumn('variant_images', 'dimensions')) {
                $table->dropColumn('dimensions');
            }

            if (Schema::hasColumn('variant_images', 'file_size')) {
                $table->dropColumn('file_size');
            }

            if (Schema::hasColumn('variant_images', 'is_active')) {
                if (Schema::hasIndex('variant_images', 'variant_images_is_active_index')) {
                    $table->dropIndex('variant_images_is_active_index');
                }

                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('variant_images', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
