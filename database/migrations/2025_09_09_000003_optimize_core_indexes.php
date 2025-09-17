<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_translations', function ($table) {
            $table->index('product_id', 'product_translations_product_idx');
            $table->index('locale', 'product_translations_locale_idx');
            $table->unique(['product_id', 'locale'], 'product_translations_unique');
        });

        Schema::table('product_attributes', function ($table) {
            $table->index('product_id', 'product_attributes_product_idx');
            $table->index('attribute_id', 'product_attributes_attribute_idx');
            $table->index('attribute_value_id', 'product_attributes_value_idx');
        });

        Schema::table('product_categories', function ($table) {
            $table->index('product_id', 'product_categories_product_idx');
            $table->index('category_id', 'product_categories_category_idx');
        });

        Schema::table('product_collections', function ($table) {
            $table->index('product_id', 'product_collections_product_idx');
            $table->index('collection_id', 'product_collections_collection_idx');
        });

        Schema::table('order_items', function ($table) {
            $table->index('order_id', 'order_items_order_idx');
            $table->index('product_id', 'order_items_product_idx');
        });

        Schema::table('prices', function ($table) {
            $table->index('currency_id', 'prices_currency_idx');
            $table->index(['priceable_type', 'priceable_id'], 'prices_priceable_idx');
        });

        Schema::table('documents', function ($table) {
            $table->index(['documentable_type', 'documentable_id'], 'documents_documentable_idx');
        });

        Schema::table('reviews', function ($table) {
            $table->index('created_at', 'reviews_created_idx');
        });

        Schema::table('users', function ($table) {
            $table->index('email_verified_at', 'users_email_verified_idx');
        });
    }

    public function down(): void
    {
        // Non-destructive
    }
};
