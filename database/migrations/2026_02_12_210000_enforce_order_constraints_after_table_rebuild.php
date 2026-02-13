<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->enforceOrdersIndexes();
        $this->enforceOrderItemsConstraintsAndIndexes();
    }

    public function down(): void
    {
        // Forward-only normalization migration.
    }

    private function enforceOrdersIndexes(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE INDEX IF NOT EXISTS "orders_status_created_at_index" ON "orders" ("status", "created_at")');
            DB::statement('CREATE INDEX IF NOT EXISTS "orders_user_created_at_index" ON "orders" ("user_id", "created_at")');

            return;
        }

        if ($driver === 'mysql') {
            $this->createMysqlIndexIfMissing('orders', ['status', 'created_at'], 'orders_status_created_at_index');
            $this->createMysqlIndexIfMissing('orders', ['user_id', 'created_at'], 'orders_user_created_at_index');

            return;
        }

        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at'], 'orders_user_created_at_index');
            });
        } catch (\Throwable $e) {
        }
    }

    private function enforceOrderItemsConstraintsAndIndexes(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite' && $this->sqliteOrderItemsNeedsRebuild()) {
            $this->rebuildSqliteOrderItemsTable();
        }

        if ($driver === 'mysql') {
            $this->ensureMysqlOrderItemsForeignKey('order_id', 'orders');
            $this->ensureMysqlOrderItemsForeignKey('product_id', 'products');

            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $this->ensureMysqlOrderItemsForeignKey('product_variant_id', 'product_variants');
            }
        }

        $this->ensureOrderItemsIndexes();
    }

    private function ensureOrderItemsIndexes(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE INDEX IF NOT EXISTS "order_items_order_id_index" ON "order_items" ("order_id")');
            DB::statement('CREATE INDEX IF NOT EXISTS "order_items_product_id_index" ON "order_items" ("product_id")');

            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                DB::statement('CREATE INDEX IF NOT EXISTS "order_items_product_variant_id_index" ON "order_items" ("product_variant_id")');
            }

            return;
        }

        if ($driver === 'mysql') {
            $this->createMysqlIndexIfMissing('order_items', ['order_id'], 'order_items_order_id_index');
            $this->createMysqlIndexIfMissing('order_items', ['product_id'], 'order_items_product_id_index');

            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $this->createMysqlIndexIfMissing(
                    'order_items',
                    ['product_variant_id'],
                    'order_items_product_variant_id_index',
                );
            }

            return;
        }

        try {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->index('order_id', 'order_items_order_id_index');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->index('product_id', 'order_items_product_id_index');
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            try {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->index('product_variant_id', 'order_items_product_variant_id_index');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    private function sqliteOrderItemsNeedsRebuild(): bool
    {
        $foreignKeys = collect(DB::select("PRAGMA foreign_key_list('order_items')"));

        if (! $this->sqliteForeignKeyMatches($foreignKeys, 'order_id', 'orders')) {
            return true;
        }

        if (! $this->sqliteForeignKeyMatches($foreignKeys, 'product_id', 'products')) {
            return true;
        }

        if (
            Schema::hasColumn('order_items', 'product_variant_id')
            && ! $this->sqliteForeignKeyMatches($foreignKeys, 'product_variant_id', 'product_variants')
        ) {
            return true;
        }

        return false;
    }

    private function sqliteForeignKeyMatches(Collection $foreignKeys, string $column, string $table): bool
    {
        return $foreignKeys->contains(function ($foreignKey) use ($column, $table): bool {
            $from = $foreignKey->from ?? $foreignKey['from'] ?? null;
            $referencedTable = $foreignKey->table ?? $foreignKey['table'] ?? null;
            $to = $foreignKey->to ?? $foreignKey['to'] ?? null;
            $onDelete = strtoupper((string) ($foreignKey->on_delete ?? $foreignKey['on_delete'] ?? ''));

            return $from === $column && $referencedTable === $table && $to === 'id' && $onDelete === 'CASCADE';
        });
    }

    private function rebuildSqliteOrderItemsTable(): void
    {
        $createSql = $this->sqliteCreateStatement('order_items');
        $columns = $this->sqliteColumnNames('order_items');

        if ($createSql === null || $columns->isEmpty()) {
            return;
        }

        $updatedSql = $this->ensureSqliteCascadeClause($createSql, 'order_id', 'orders');
        $updatedSql = $this->ensureSqliteCascadeClause($updatedSql, 'product_id', 'products');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $updatedSql = $this->ensureSqliteCascadeClause($updatedSql, 'product_variant_id', 'product_variants');
        }

        if ($updatedSql === $createSql) {
            return;
        }

        $indexes = $this->sqliteIndexesWithDetails('order_items');

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            if (Schema::hasTable('order_items_temp_constraints')) {
                Schema::drop('order_items_temp_constraints');
            }

            DB::statement('ALTER TABLE "order_items" RENAME TO "order_items_temp_constraints"');
            DB::statement($updatedSql);

            $columnList = $columns
                ->map(static fn (string $column): string => '"' . $column . '"')
                ->implode(', ');

            DB::statement(
                sprintf(
                    'INSERT INTO "order_items" (%1$s) SELECT %1$s FROM "order_items_temp_constraints"',
                    $columnList,
                ),
            );

            Schema::drop('order_items_temp_constraints');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->recreateSqliteIndexes('order_items', $indexes);
    }

    private function ensureSqliteCascadeClause(string $createSql, string $column, string $referencedTable): string
    {
        $pattern = sprintf(
            '/foreign\\s+key\\(\\s*"%1$s"\\s*\\)\\s*references\\s*"%2$s"\\s*\\(\\s*"id"\\s*\\)(?:\\s+on\\s+delete\\s+\\w+)?/i',
            preg_quote($column, '/'),
            preg_quote($referencedTable, '/'),
        );

        if (preg_match($pattern, $createSql, $matches) === 1) {
            $clause = $matches[0];

            if (stripos($clause, 'ON DELETE CASCADE') !== false) {
                return $createSql;
            }

            $replacement = stripos($clause, 'ON DELETE') !== false
                ? preg_replace('/on\\s+delete\\s+\\w+/i', 'ON DELETE CASCADE', $clause)
                : $clause . ' ON DELETE CASCADE';

            if (is_string($replacement) && $replacement !== '') {
                return str_replace($clause, $replacement, $createSql);
            }

            return $createSql;
        }

        $insertionPoint = strrpos($createSql, ')');

        if ($insertionPoint === false) {
            return $createSql;
        }

        $before = rtrim(substr($createSql, 0, $insertionPoint));
        $after = substr($createSql, $insertionPoint);

        if (! str_ends_with($before, ',')) {
            $before .= ',';
        }

        $before .= sprintf(
            "\n  foreign key(\"%s\") references \"%s\"(\"id\") on delete cascade",
            $column,
            $referencedTable,
        );

        return $before . "\n" . $after;
    }

    private function sqliteCreateStatement(string $table): ?string
    {
        $statement = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);
        $sql = $statement->sql ?? null;

        return is_string($sql) && $sql !== '' ? $sql : null;
    }

    /**
     * @return Collection<int, string>
     */
    private function sqliteColumnNames(string $table): Collection
    {
        return collect(DB::select("PRAGMA table_info('{$table}')"))
            ->map(static fn ($column): ?string => $column->name ?? $column['name'] ?? null)
            ->filter(static fn (?string $name): bool => is_string($name) && $name !== '')
            ->values();
    }

    /**
     * @return Collection<int, array{name: string|null, columns: array<int, string|null>, unique: bool}>
     */
    private function sqliteIndexesWithDetails(string $table): Collection
    {
        return collect(DB::select("PRAGMA index_list('{$table}')"))
            ->map(function ($index): array {
                $name = $index->name ?? $index['name'] ?? null;

                if (! is_string($name) || $name === '') {
                    return ['name' => null, 'columns' => [], 'unique' => false];
                }

                $details = DB::select("PRAGMA index_info('{$name}')");

                return [
                    'name' => $name,
                    'columns' => array_map(
                        static fn ($detail) => $detail->name ?? $detail['name'] ?? null,
                        $details,
                    ),
                    'unique' => (bool) ($index->unique ?? $index['unique'] ?? false),
                ];
            });
    }

    /**
     * @param  Collection<int, array{name: string|null, columns: array<int, string|null>, unique: bool}>  $indexes
     */
    private function recreateSqliteIndexes(string $table, Collection $indexes): void
    {
        foreach ($indexes as $index) {
            $name = $index['name'] ?? null;
            $columns = array_values(array_filter($index['columns'] ?? []));

            if (! is_string($name) || $name === '' || $columns === []) {
                continue;
            }

            $quotedColumns = implode('", "', $columns);

            DB::statement(sprintf(
                'CREATE %sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")',
                ! empty($index['unique']) ? 'UNIQUE ' : '',
                $name,
                $table,
                $quotedColumns,
            ));
        }
    }

    private function createMysqlIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if ($this->mysqlHasIndex($table, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function mysqlHasIndex(string $table, array $columns): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        $indexes = DB::select(
            <<<'SQL'
SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS indexed_columns
FROM information_schema.statistics
WHERE TABLE_SCHEMA = ?
  AND TABLE_NAME = ?
GROUP BY INDEX_NAME
SQL,
            [$database, $table],
        );

        return collect($indexes)
            ->map(fn ($index): array => explode(',', (string) ($index->indexed_columns ?? '')))
            ->contains(fn (array $indexedColumns): bool => $indexedColumns === $columns);
    }

    private function ensureMysqlOrderItemsForeignKey(string $column, string $referencedTable): void
    {
        $database = Schema::getConnection()->getDatabaseName();

        $metadata = DB::selectOne(
            <<<'SQL'
SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS rc
JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
    AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
WHERE rc.CONSTRAINT_SCHEMA = ?
  AND kcu.TABLE_NAME = 'order_items'
  AND kcu.COLUMN_NAME = ?
LIMIT 1
SQL,
            [$database, $column],
        );

        if ($metadata !== null && strtoupper((string) ($metadata->DELETE_RULE ?? '')) === 'CASCADE') {
            return;
        }

        $constraintName = $metadata->CONSTRAINT_NAME ?? null;

        if (is_string($constraintName) && $constraintName !== '') {
            Schema::table('order_items', function (Blueprint $table) use ($constraintName): void {
                $table->dropForeign($constraintName);
            });
        }

        Schema::table('order_items', function (Blueprint $table) use ($column, $referencedTable): void {
            $table->foreign($column)->references('id')->on($referencedTable)->cascadeOnDelete();
        });
    }
};
