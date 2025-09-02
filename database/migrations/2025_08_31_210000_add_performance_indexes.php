<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_products')) {
            $this->createIndex('sh_products', 'sh_products_published_at_index', ['published_at']);
            if (Schema::hasColumn('sh_products', 'is_visible')) {
                $this->createIndex('sh_products', 'sh_products_visible_published_index', ['is_visible', 'published_at']);
            }
            if (Schema::hasColumn('sh_products', 'brand_id')) {
                $this->createIndex('sh_products', 'sh_products_brand_id_index', ['brand_id']);
            }
        }

        if (Schema::hasTable('sh_category_product')) {
            $this->createIndex('sh_category_product', 'sh_cat_prod_cat_prod_index', ['category_id', 'product_id']);
            $this->createIndex('sh_category_product', 'sh_cat_prod_product_id_index', ['product_id']);
        }

        if (Schema::hasTable('sh_collection_product')) {
            $this->createIndex('sh_collection_product', 'sh_coll_prod_coll_prod_index', ['collection_id', 'product_id']);
            $this->createIndex('sh_collection_product', 'sh_coll_prod_product_id_index', ['product_id']);
        }

        if (Schema::hasTable('sh_product_translations')) {
            $this->createIndex('sh_product_translations', 'sh_prod_trans_product_id_index', ['product_id']);
            $this->createIndex('sh_product_translations', 'sh_prod_trans_locale_index', ['locale']);

            if (DB::getDriverName() === 'mysql') {
                $columns = collect(['name', 'summary', 'description'])
                    ->filter(fn ($c) => Schema::hasColumn('sh_product_translations', $c))
                    ->values()
                    ->all();

                if (! empty($columns)) {
                    $indexName = 'sh_prod_trans_fulltext_' . substr(md5(implode(',', $columns)), 0, 8);
                    if (! $this->indexNameExists('sh_product_translations', $indexName)) {
                        try {
                            DB::statement('ALTER TABLE `sh_product_translations` ADD FULLTEXT `'.$indexName.'` (`'.implode('`,`', $columns).'`)');
                        } catch (Throwable $e) {
                            // ignore if not supported
                        }
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Index drops are optional and safe to skip in down()
    }

    private function createIndex(string $table, string $indexName, array $columns): void
    {
        if ($this->indexNameExists($table, $indexName)) {
            return;
        }
        $driver = DB::getDriverName();
        $cols = '`' . implode('`,`', $columns) . '`';
        try {
            if ($driver === 'mysql') {
                DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$cols})");
            } elseif ($driver === 'sqlite') {
                DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} (" . implode(',', $columns) . ")");
            } else {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    try { $t->index($columns, $indexName); } catch (Throwable $e) {}
                });
            }
        } catch (Throwable $e) {}
    }

    private function indexNameExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                $db = DB::getDatabaseName();
                $rows = DB::select('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1', [$db, $table, $indexName]);
                return !empty($rows);
            } elseif ($driver === 'sqlite') {
                $rows = DB::select('PRAGMA index_list(' . $table . ')');
                foreach ($rows as $r) {
                    if (!empty($r->name) && $r->name === $indexName) return true;
                }
                return false;
            }
        } catch (Throwable $e) {
            return false;
        }
        return false;
    }
};


