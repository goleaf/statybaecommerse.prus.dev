<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the missing hash column and soft delete support for variant combinations.
     */
    public function up(): void
    {
        Schema::table('variant_combinations', function (Blueprint $table): void {
            if (! Schema::hasColumn('variant_combinations', 'combination_hash')) {
                $table->string('combination_hash', 64)->nullable()->after('attribute_combinations');
                $table->index(['product_id', 'combination_hash']);
            }

            if (! Schema::hasColumn('variant_combinations', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Roll back the migration changes.
     */
    public function down(): void
    {
        Schema::table('variant_combinations', function (Blueprint $table): void {
            if (Schema::hasColumn('variant_combinations', 'combination_hash')) {
                $table->dropIndex('variant_combinations_product_id_combination_hash_index');
                $table->dropColumn('combination_hash');
            }

            if (Schema::hasColumn('variant_combinations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
