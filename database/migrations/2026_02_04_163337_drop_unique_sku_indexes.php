<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS products_sku_unique');
                DB::statement('CREATE INDEX IF NOT EXISTS products_sku_index ON products (sku)');
            } else {
                Schema::table('products', function (Blueprint $table): void {
                    $table->dropUnique(['sku']);
                    $table->index(['sku']);
                });
            }
        }

        if (Schema::hasTable('product_variants')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS product_variants_sku_unique');
                DB::statement('CREATE INDEX IF NOT EXISTS product_variants_sku_index ON product_variants (sku)');
            } else {
                Schema::table('product_variants', function (Blueprint $table): void {
                    $table->dropUnique(['sku']);
                    $table->index(['sku']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS products_sku_index');
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS products_sku_unique ON products (sku)');
            } else {
                Schema::table('products', function (Blueprint $table): void {
                    $table->dropIndex(['sku']);
                    $table->unique(['sku']);
                });
            }
        }

        if (Schema::hasTable('product_variants')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS product_variants_sku_index');
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS product_variants_sku_unique ON product_variants (sku)');
            } else {
                Schema::table('product_variants', function (Blueprint $table): void {
                    $table->dropIndex(['sku']);
                    $table->unique(['sku']);
                });
            }
        }
    }
};
