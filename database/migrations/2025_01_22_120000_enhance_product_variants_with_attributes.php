<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create attributes table if not exists
        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('select'); // text, number, select, boolean, color
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(true);
                $table->boolean('is_searchable')->default(false);
                $table->boolean('is_variant')->default(true); // Can be used for product variants
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->json('options')->nullable(); // For select type attributes
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_enabled', 'sort_order']);
                $table->index(['is_variant', 'is_enabled']);
            });
        }

        // Create attribute_values table if not exists
        if (!Schema::hasTable('attribute_values')) {
            Schema::create('attribute_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value');
                $table->string('slug');
                $table->string('color_code')->nullable(); // For color attributes
                $table->integer('sort_order')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
                $table->unique(['attribute_id', 'slug']);
                $table->index(['attribute_id', 'is_enabled']);
            });
        }

        // Create product_variant_attributes pivot table if not exists
        if (!Schema::hasTable('product_variant_attributes')) {
            Schema::create('product_variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_value_id');
                $table->timestamps();

                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
                $table->unique(['variant_id', 'attribute_value_id'], 'variant_attribute_value_unique');
            });
        }

        // Create attribute_translations table
        if (!Schema::hasTable('attribute_translations')) {
            Schema::create('attribute_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('locale', 5);
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
                $table->unique(['attribute_id', 'locale']);
                $table->index(['locale']);
            });
        }

        // Create attribute_value_translations table
        if (!Schema::hasTable('attribute_value_translations')) {
            Schema::create('attribute_value_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attribute_value_id');
                $table->string('locale', 5);
                $table->string('value');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
                $table->unique(['attribute_value_id', 'locale']);
                $table->index(['locale']);
            });
        }

        // Enhance product_variants table if needed
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                // Add size-specific fields if they don't exist
                if (!Schema::hasColumn('product_variants', 'size')) {
                    $table->string('size')->nullable()->after('name');
                }
                if (!Schema::hasColumn('product_variants', 'size_price_modifier')) {
                    $table->decimal('size_price_modifier', 8, 2)->default(0)->after('cost_price');
                }
                if (!Schema::hasColumn('product_variants', 'is_default_variant')) {
                    $table->boolean('is_default_variant')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('product_variants', 'variant_attributes')) {
                    $table->json('variant_attributes')->nullable()->after('is_default_variant');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_translations');
        Schema::dropIfExists('attribute_translations');
        Schema::dropIfExists('product_variant_attributes');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn([
                    'size',
                    'size_price_modifier',
                    'is_default_variant',
                    'variant_attributes'
                ]);
            });
        }
    }
};
