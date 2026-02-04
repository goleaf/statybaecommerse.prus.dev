<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'metadata')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        if (Schema::hasColumn('product_variants', 'variant_metadata')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('variant_metadata');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('video_url');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('variant_metadata')->nullable()->after('variant_attribute_matrix');
        });
    }
};
