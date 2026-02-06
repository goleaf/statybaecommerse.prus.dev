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

        // Drop status-related indexes before touching the status column on SQLite.
        $this->dropStatusIndexes();

        $hasStatus = Schema::hasColumn('products', 'status');
        $hasStatusNew = Schema::hasColumn('products', 'status_new');

        if ($hasStatus && ! $hasStatusNew) {
            Schema::table('products', function (Blueprint $table): void {
                // Add a temporary column that will store the normalized status values.
                $table->string('status_new', 32)->default(ProductStatus::DRAFT->value);
            });

            $hasStatusNew = true;
        }

        if ($hasStatus && $hasStatusNew) {
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
        }

        if ($hasStatus) {
            Schema::table('products', function (Blueprint $table): void {
                // Remove the legacy enum column now that the replacement data column is populated.
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('products', 'status_new') && ! Schema::hasColumn('products', 'status')) {
            Schema::table('products', function (Blueprint $table): void {
                // Promote the temporary column to become the canonical status column.
                $table->renameColumn('status_new', 'status');
            });
        }

        $this->ensureStatusPublishedAtIndex();
    }

    /**
     * Revert the status column to the original enum definition with the historic values.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $this->dropStatusIndexes();

        $hasStatus = Schema::hasColumn('products', 'status');
        $hasStatusOld = Schema::hasColumn('products', 'status_old');

        if ($hasStatus && ! $hasStatusOld) {
            Schema::table('products', function (Blueprint $table): void {
                // Introduce a legacy enum column so data can be remapped to the earlier constraint.
                $table->enum('status_old', ['draft', 'published', 'archived'])->default('draft');
            });

            $hasStatusOld = true;
        }

        if ($hasStatus && $hasStatusOld) {
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
        }

        if ($hasStatus) {
            Schema::table('products', function (Blueprint $table): void {
                // Drop the expanded column now that the historical enum values are restored.
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('products', 'status_old') && ! Schema::hasColumn('products', 'status')) {
            Schema::table('products', function (Blueprint $table): void {
                // Move the legacy column into place as the authoritative status attribute.
                $table->renameColumn('status_old', 'status');
            });
        }

        $this->ensureStatusPublishedAtIndex();
    }

    /**
     * Attempt to drop status-related indexes if they exist, ignoring missing-index exceptions.
     */
    private function dropStatusIndexes(): void
    {
        foreach ([
            'products_status_is_visible_index',
            'products_status_published_at_index',
            'products_storefront_visibility_idx',
        ] as $indexName) {
            try {
                Schema::table('products', function (Blueprint $table) use ($indexName): void {
                    $table->dropIndex($indexName);
                });
            } catch (\Throwable $exception) {
                // The index might not exist yet (fresh databases) or may already be removed; swallow the error.
            }
        }
    }

    /**
     * Ensure the canonical product status index exists.
     */
    private function ensureStatusPublishedAtIndex(): void
    {
        if (! Schema::hasColumn('products', 'status') || ! Schema::hasColumn('products', 'published_at')) {
            return;
        }

        try {
            Schema::table('products', function (Blueprint $table): void {
                $table->index(['status', 'published_at']);
            });
        } catch (\Throwable $exception) {
            // Index is already present.
        }
    }
};
