<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureIndexes('product_translations', [
            ['columns' => ['product_id'], 'name' => 'product_translations_product_idx'],
            ['columns' => ['locale'], 'name' => 'product_translations_locale_idx'],
            ['columns' => ['product_id', 'locale'], 'name' => 'product_translations_unique', 'unique' => true],
        ]);

        $this->ensureIndexes('product_attributes', [
            ['columns' => ['product_id'], 'name' => 'product_attributes_product_idx'],
            ['columns' => ['attribute_id'], 'name' => 'product_attributes_attribute_idx'],
            ['columns' => ['attribute_value_id'], 'name' => 'product_attributes_value_idx'],
        ]);

        $this->ensureIndexes('product_categories', [
            ['columns' => ['product_id'], 'name' => 'product_categories_product_idx'],
            ['columns' => ['category_id'], 'name' => 'product_categories_category_idx'],
        ]);

        $this->ensureIndexes('product_collections', [
            ['columns' => ['product_id'], 'name' => 'product_collections_product_idx'],
            ['columns' => ['collection_id'], 'name' => 'product_collections_collection_idx'],
        ]);

        $this->ensureIndexes('order_items', [
            ['columns' => ['order_id'], 'name' => 'order_items_order_idx'],
            ['columns' => ['product_id'], 'name' => 'order_items_product_idx'],
        ]);

        $this->ensureIndexes('prices', [
            ['columns' => ['currency_id'], 'name' => 'prices_currency_idx'],
            ['columns' => ['priceable_type', 'priceable_id'], 'name' => 'prices_priceable_idx'],
        ]);

        $this->ensureIndexes('documents', [
            ['columns' => ['documentable_type', 'documentable_id'], 'name' => 'documents_documentable_idx'],
        ]);

        $this->ensureIndexes('reviews', [
            ['columns' => ['created_at'], 'name' => 'reviews_created_idx'],
        ]);

        $this->ensureIndexes('users', [
            ['columns' => ['email_verified_at'], 'name' => 'users_email_verified_idx'],
        ]);
    }

    public function down(): void
    {
        // Non-destructive on purpose
    }

    /**
     * @param array<int, array{columns: array<int, string>, name: string, unique?: bool}> $indexes
     */
    private function ensureIndexes(string $table, array $indexes): void
    {
        if (! Schema::hasTable($table) || $indexes === []) {
            return;
        }

        foreach ($indexes as $index) {
            $columns = $index['columns'];
            $name = $index['name'];
            $unique = $index['unique'] ?? false;

            if ($this->indexExists($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) use ($columns, $name, $unique): void {
                if ($unique) {
                    $table->unique($columns, $name);
                } else {
                    $table->index($columns, $name);
                }
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("PRAGMA index_list('{$table}')");

            foreach ($result as $row) {
                $name = $row->name ?? ($row['name'] ?? null);

                if ($name === $index) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return ! empty($result);
        }

        return false;
    }
};
