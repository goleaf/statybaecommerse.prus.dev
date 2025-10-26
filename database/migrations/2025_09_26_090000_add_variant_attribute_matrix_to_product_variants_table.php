<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        Schema::table('product_variants', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_variants', 'variant_attribute_matrix')) {
                $table->json('variant_attribute_matrix')->nullable()->after('attributes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        Schema::table('product_variants', function (Blueprint $table): void {
            if (Schema::hasColumn('product_variants', 'variant_attribute_matrix')) {
                $table->dropColumn('variant_attribute_matrix');
            }
        });
    }
};
