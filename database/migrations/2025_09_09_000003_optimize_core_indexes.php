<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite-only safe index creations (project uses SQLite)

        // product_translations
        if (Schema::hasTable('product_translations')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_translations_product_idx ON product_translations (product_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_translations_locale_idx ON product_translations (locale)');
            $this->createIndex('CREATE UNIQUE INDEX IF NOT EXISTS product_translations_unique ON product_translations (product_id, locale)');
        }

        // product_attributes
        if (Schema::hasTable('product_attributes')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_attributes_product_idx ON product_attributes (product_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_attributes_attribute_idx ON product_attributes (attribute_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_attributes_value_idx ON product_attributes (attribute_value_id)');
        }

        // product_categories
        if (Schema::hasTable('product_categories')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_categories_product_idx ON product_categories (product_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_categories_category_idx ON product_categories (category_id)');
        }

        // product_collections
        if (Schema::hasTable('product_collections')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_collections_product_idx ON product_collections (product_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS product_collections_collection_idx ON product_collections (collection_id)');
        }

        // order_items
        if (Schema::hasTable('order_items')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS order_items_order_idx ON order_items (order_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS order_items_product_idx ON order_items (product_id)');
        }

        // prices (if exists)
        if (Schema::hasTable('prices')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS prices_currency_idx ON prices (currency_id)');
            $this->createIndex('CREATE INDEX IF NOT EXISTS prices_priceable_idx ON prices (priceable_type, priceable_id)');
        }

        // documents (if exists)
        if (Schema::hasTable('documents')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS documents_documentable_idx ON documents (documentable_type, documentable_id)');
        }

        // reviews (additional)
        if (Schema::hasTable('reviews')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS reviews_created_idx ON reviews (created_at)');
        }

        // users
        if (Schema::hasTable('users')) {
            $this->createIndex('CREATE INDEX IF NOT EXISTS users_email_verified_idx ON users (email_verified_at)');
        }
    }

    public function down(): void
    {
        // Non-destructive on purpose
    }

    private function createIndex(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {  /* ignore */
        }
    }
};
