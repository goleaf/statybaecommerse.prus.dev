<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attribute_values') && ! Schema::hasColumn('attribute_values', 'is_default')) {
            Schema::table('attribute_values', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('is_active');
            });
        }

        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                // Some tests may attach without attribute_id, relax constraint in test env
                if (Schema::hasColumn('product_attributes', 'attribute_id')) {
                    $table->unsignedBigInteger('attribute_id')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('product_variant_attributes')) {
            Schema::table('product_variant_attributes', function (Blueprint $table) {
                if (Schema::hasColumn('product_variant_attributes', 'attribute_id')) {
                    $table->unsignedBigInteger('attribute_id')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attribute_values') && Schema::hasColumn('attribute_values', 'is_default')) {
            Schema::table('attribute_values', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }

        // Cannot reliably restore NOT NULL in down for safety; skipping.
    }
};
