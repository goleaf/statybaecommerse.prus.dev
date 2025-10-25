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
        if (! Schema::hasTable('product_images')) {
            return;
        }

        Schema::table('product_images', function (Blueprint $table): void {
            // Add the optional variant reference so gallery images can be tied to specific variants when present.
            if (! Schema::hasColumn('product_images', 'product_variant_id')) {
                $table
                    ->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }

            // Introduce dedicated textual metadata columns used by the refreshed model API.
            if (! Schema::hasColumn('product_images', 'title')) {
                $table->string('title')->nullable()->after('path');
            }

            if (! Schema::hasColumn('product_images', 'alt')) {
                $table->string('alt')->nullable()->after('title');
            }

            // Replace the previous sort_order column with a clearer position attribute while leaving the legacy column untouched for now.
            if (! Schema::hasColumn('product_images', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('alt')->index();
            }

            // Store arbitrary rendering hints and responsive derivatives alongside each image in a JSON column.
            if (! Schema::hasColumn('product_images', 'meta')) {
                $table->json('meta')->nullable()->after('position');
            }
        });

        // Backfill the new attributes from their legacy counterparts so existing records remain consistent.
        DB::table('product_images')
            ->select('id', 'alt_text', 'sort_order')
            ->orderBy('id')
            ->chunkById(100, static function ($images): void {
                foreach ($images as $image) {
                    $updates = [];

                    if (isset($image->alt_text) && $image->alt_text !== '') {
                        $updates['title'] ??= $image->alt_text;
                        $updates['alt'] ??= $image->alt_text;
                    }

                    if (isset($image->sort_order)) {
                        $updates['position'] = (int) $image->sort_order;
                    }

                    if ($updates !== []) {
                        DB::table('product_images')->where('id', $image->id)->update($updates);
                    }
                }
            });

    }

    public function down(): void
    {
        if (! Schema::hasTable('product_images')) {
            return;
        }

        Schema::table('product_images', function (Blueprint $table): void {
            // Drop the foreign key before removing the column to satisfy strict SQL engines.
            if (Schema::hasColumn('product_images', 'product_variant_id')) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            }

            if (Schema::hasColumn('product_images', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('product_images', 'alt')) {
                $table->dropColumn('alt');
            }

            if (Schema::hasColumn('product_images', 'position')) {
                $table->dropColumn('position');
            }

            if (Schema::hasColumn('product_images', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
