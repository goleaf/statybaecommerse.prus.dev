<?php

declare(strict_types=1);

use App\Models\VariantCombination;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_combinations', function (Blueprint $table): void {
            if (! Schema::hasColumn('variant_combinations', 'combination_hash')) {
                // Store the deterministic hash alongside the combination payload for quick lookups.
                $table->string('combination_hash', 64)->nullable()->after('attribute_combinations');
                $table->index(['product_id', 'combination_hash'], 'variant_combinations_product_hash_index');
            }

            if (! Schema::hasColumn('variant_combinations', 'deleted_at')) {
                // Enable soft deletes so historical combinations remain recoverable.
                $table->softDeletes();
            }
        });

        // Backfill deterministic hashes for any existing rows.
        VariantCombination::withTrashed()->each(function (VariantCombination $combination): void {
            if (! blank($combination->combination_hash)) {
                return;
            }

            // The accessor now generates the deterministic value automatically.
            $combination->forceFill([
                'combination_hash' => $combination->combination_hash,
            ])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('variant_combinations', function (Blueprint $table): void {
            if (Schema::hasColumn('variant_combinations', 'combination_hash')) {
                try {
                    // Guard against platform-specific errors when the index is already absent.
                    $table->dropIndex('variant_combinations_product_hash_index');
                } catch (\Throwable $exception) {
                    // Swallow the exception because SQLite in memory tables may omit named indexes when rolling back.
                }

                $table->dropColumn('combination_hash');
            }

            if (Schema::hasColumn('variant_combinations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
