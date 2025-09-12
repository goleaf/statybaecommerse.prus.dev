<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUniqueIndex('sh_brand_translations', 'sh_brand_translations_locale_slug_unique', ['locale', 'slug']);
        $this->ensureUniqueIndex('sh_category_translations', 'sh_category_translations_locale_slug_unique', ['locale', 'slug']);
        $this->ensureUniqueIndex('sh_collection_translations', 'sh_collection_translations_locale_slug_unique', ['locale', 'slug']);
        $this->ensureUniqueIndex('sh_product_translations', 'sh_product_translations_locale_slug_unique', ['locale', 'slug']);
        $this->ensureUniqueIndex('sh_legal_translations', 'sh_legal_translations_locale_slug_unique', ['locale', 'slug']);
    }

    public function down(): void
    {
        $this->dropUniqueIfExists('sh_brand_translations', 'sh_brand_translations_locale_slug_unique');
        $this->dropUniqueIfExists('sh_category_translations', 'sh_category_translations_locale_slug_unique');
        $this->dropUniqueIfExists('sh_collection_translations', 'sh_collection_translations_locale_slug_unique');
        $this->dropUniqueIfExists('sh_product_translations', 'sh_product_translations_locale_slug_unique');
        $this->dropUniqueIfExists('sh_legal_translations', 'sh_legal_translations_locale_slug_unique');
    }

    private function ensureUniqueIndex(string $table, string $indexName, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns) {
            $table->unique($columns);
        });
    }

    private function dropUniqueIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $result = DB::selectOne("SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?", [$indexName]);

            return $result !== null;
        }

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $db = DB::getDatabaseName();
            $result = DB::selectOne(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$db, $table, $indexName]
            );

            return $result !== null;
        }

        // Fallback: attempt and ignore errors
        try {
            Schema::table($table, function () {});

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
