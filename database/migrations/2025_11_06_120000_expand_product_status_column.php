<?php

declare(strict_types=1);

use App\Enums\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand the product status column so new enum values like "active" persist without constraint errors.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        // Drop the legacy index referencing the old enum column to prevent constraint conflicts during alteration.
        $this->dropStatusVisibilityIndex();

        Schema::table('products', function (Blueprint $table): void {
            // Add a temporary column that will store the normalized status values.
            $table->string('status_new', 32)->default(ProductStatus::DRAFT->value);
        });

        DB::table('products')
            ->select(['id', 'status'])
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                /** @var iterable<int, object{id:int,status:mixed}> $products */
                foreach ($products as $product) {
                    $rawStatus = is_string($product->status) ? $product->status : null;

                    $normalized = match ($rawStatus) {
                        'published' => ProductStatus::ACTIVE->value,
                        null, '' => ProductStatus::DRAFT->value,
                        default => $rawStatus,
                    };

                    DB::table('products')
                        ->where('id', (int) $product->id)
                        ->update(['status_new' => $normalized]);
                }
            });

        Schema::table('products', function (Blueprint $table): void {
            // Remove the legacy enum column now that the replacement data column is populated.
            $table->dropColumn('status');
        });

        Schema::table('products', function (Blueprint $table): void {
            // Promote the temporary column to become the canonical status column.
            $table->renameColumn('status_new', 'status');
        });

        Schema::table('products', function (Blueprint $table): void {
            // Recreate the index so storefront queries keep benefitting from the status filter.
            $table->index(['status', 'is_visible']);
        });
    }

    /**
     * Revert the status column to the original enum definition with the historic values.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $this->dropStatusVisibilityIndex();

        Schema::table('products', function (Blueprint $table): void {
            // Introduce a legacy enum column so data can be remapped to the earlier constraint.
            $table->enum('status_old', ['draft', 'published', 'archived'])->default('draft');
        });

        DB::table('products')
            ->select(['id', 'status'])
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                /** @var iterable<int, object{id:int,status:mixed}> $products */
                foreach ($products as $product) {
                    $rawStatus = is_string($product->status) ? $product->status : null;

                    $legacy = match ($rawStatus) {
                        ProductStatus::ACTIVE->value   => 'published',
                        ProductStatus::ARCHIVED->value => 'archived',
                        default                        => 'draft',
                    };

                    DB::table('products')
                        ->where('id', (int) $product->id)
                        ->update(['status_old' => $legacy]);
                }
            });

        Schema::table('products', function (Blueprint $table): void {
            // Drop the expanded column now that the historical enum values are restored.
            $table->dropColumn('status');
        });

        Schema::table('products', function (Blueprint $table): void {
            // Move the legacy column into place as the authoritative status attribute.
            $table->renameColumn('status_old', 'status');
        });

        Schema::table('products', function (Blueprint $table): void {
            // Reinstate the original index structure for downstream lookups.
            $table->index(['status', 'is_visible']);
        });
    }

    /**
     * Attempt to drop the status visibility index if it exists, ignoring missing-index exceptions.
     */
    private function dropStatusVisibilityIndex(): void
    {
        try {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropIndex('products_status_is_visible_index');
            });
        } catch (\Throwable $exception) {
            // The index might not exist yet (fresh databases) or may already be removed; swallow the error.
        }
    }
};
