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
        $this->enforceOrdersConstraints();
        $this->enforceOrderItemsConstraints();
    }

    public function down(): void
    {
        throw new RuntimeException('This migration cannot be rolled back.');
    }

    private function enforceOrdersConstraints(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->ensureSqliteOrdersForeignKey();
            $this->ensureSqliteOrdersIndexes();

            return;
        }

        if ($driver === 'mysql') {
            $this->ensureMysqlOrdersForeignKey();
            $this->ensureMysqlOrdersIndexes();

            return;
        }

        $this->ensureGenericOrdersIndexes();
    }

    private function enforceOrderItemsConstraints(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->ensureSqliteOrderItemsForeignKeys();
            $this->ensureSqliteOrderItemsIndexes();

            return;
        }

        if ($driver === 'mysql') {
            $this->ensureMysqlOrderItemsForeignKeys();
            $this->ensureMysqlOrderItemsIndexes();

            return;
        }

        $this->ensureGenericOrderItemsIndexes();
    }

    private function ensureSqliteOrdersForeignKey(): void
    {
        $userForeignKey = $this->sqliteForeignKeys('orders')
            ->first(function (array $foreignKey): bool {
                return ($foreignKey['from'] ?? null) === 'user_id'
                    && ($foreignKey['table'] ?? null) === 'users'
                    && ($foreignKey['to'] ?? null) === 'id'
                    && strtoupper((string) ($foreignKey['on_delete'] ?? '')) === 'SET NULL';
            });

        if ($userForeignKey !== null) {
            return;
        }

        $this->rebuildSqliteOrdersTable();
    }

    private function ensureSqliteOrdersIndexes(): void
    {
        $this->createSqliteIndexIfMissing(
            'orders_status_created_at_index',
            'orders',
            ['status', 'created_at'],
        );

        $this->createSqliteIndexIfMissing(
            'orders_user_created_at_index',
            'orders',
            ['user_id', 'created_at'],
        );
    }

    private function ensureMysqlOrdersForeignKey(): void
    {
        $metadata = $this->mysqlForeignKeyMetadata('orders', 'user_id');

        if ($metadata === null) {
            $this->addMysqlOrderUserForeignKey();

            return;
        }

        $deleteRule = strtoupper((string) ($metadata->DELETE_RULE ?? ''));

        if ($deleteRule === 'SET NULL') {
            return;
        }

        $constraintName = $metadata->CONSTRAINT_NAME ?? null;

        if (is_string($constraintName) && $constraintName !== '') {
            Schema::table('orders', function (Blueprint $table) use ($constraintName): void {
                $table->dropForeign($constraintName);
            });
        }

        $this->addMysqlOrderUserForeignKey();
    }

    private function ensureMysqlOrdersIndexes(): void
    {
        $this->createMysqlIndexIfMissing(
            'orders',
            ['status', 'created_at'],
            'orders_status_created_at_index',
        );

        $this->createMysqlIndexIfMissing(
            'orders',
            ['user_id', 'created_at'],
            'orders_user_created_at_index',
        );
    }

    private function ensureGenericOrdersIndexes(): void
    {
        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            });
        } catch (\Throwable $e) {
            // Index likely already exists – ignore.
        }

        try {
            Schema::table('orders', function (Blueprint $table): void {
                $table->index(['user_id', 'created_at'], 'orders_user_created_at_index');
            });
        } catch (\Throwable $e) {
            // Index likely already exists – ignore.
        }
    }

    private function ensureSqliteOrderItemsForeignKeys(): void
    {
        if (! $this->sqliteOrderItemsForeignKeysAreValid()) {
            $this->rebuildSqliteOrderItemsTable();
        }
    }

    private function ensureSqliteOrderItemsIndexes(): void
    {
        $this->createSqliteIndexIfMissing(
            'order_items_order_id_index',
            'order_items',
            ['order_id'],
        );

        $this->createSqliteIndexIfMissing(
            'order_items_product_id_index',
            'order_items',
            ['product_id'],
        );

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $this->createSqliteIndexIfMissing(
                'order_items_product_variant_id_index',
                'order_items',
                ['product_variant_id'],
            );
        }
    }

    private function ensureMysqlOrderItemsForeignKeys(): void
    {
        $this->ensureMysqlOrderItemForeignKey('order_id', 'orders');
        $this->ensureMysqlOrderItemForeignKey('product_id', 'products');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $this->ensureMysqlOrderItemForeignKey('product_variant_id', 'product_variants');
        }
    }

    private function ensureMysqlOrderItemsIndexes(): void
    {
        $this->createMysqlIndexIfMissing(
            'order_items',
            ['order_id'],
            'order_items_order_id_index',
        );

        $this->createMysqlIndexIfMissing(
            'order_items',
            ['product_id'],
            'order_items_product_id_index',
        );

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $this->createMysqlIndexIfMissing(
                'order_items',
                ['product_variant_id'],
                'order_items_product_variant_id_index',
            );
        }
    }

    private function ensureGenericOrderItemsIndexes(): void
    {
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

    private function sqliteOrderItemsForeignKeysAreValid(): bool
    {
        $foreignKeys = $this->sqliteForeignKeys('order_items');

        $expected = collect([
            ['column' => 'order_id', 'table' => 'orders'],
            ['column' => 'product_id', 'table' => 'products'],
        ]);

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $expected = $expected->push(['column' => 'product_variant_id', 'table' => 'product_variants']);
        }

        foreach ($expected as $requirement) {
            $match = $foreignKeys->first(function (array $foreignKey) use ($requirement): bool {
                return ($foreignKey['from'] ?? null) === $requirement['column']
                    && ($foreignKey['table'] ?? null) === $requirement['table']
                    && strtoupper((string) ($foreignKey['on_delete'] ?? '')) === 'CASCADE';
            });

            if ($match === null) {
                return false;
            }
        }

        return true;
    }

    private function rebuildSqliteOrdersTable(): void
    {
        $columns = $this->sqliteColumnNames('orders');

        if ($columns->isEmpty()) {
            return;
        }

        $createSql = $this->sqliteCreateStatement('orders');

        if ($createSql === null) {
            return;
        }

        $updatedSql = $this->ensureSqliteOrderCreateSqlHasUserFk($createSql);

        if ($updatedSql === $createSql) {
            return;
        }

        $indexes = $this->sqliteIndexesWithDetails('orders');

        if (Schema::hasTable('orders_temp')) {
            Schema::drop('orders_temp');
        }

        DB::transaction(function () use ($columns, $updatedSql): void {
            DB::statement('ALTER TABLE "orders" RENAME TO "orders_temp"');
            DB::statement($updatedSql);

            $columnList = $columns
                ->map(static fn (string $column): string => '"' . $column . '"')
                ->implode(', ');

            DB::statement(sprintf(
                'INSERT INTO "orders" (%1$s) SELECT %1$s FROM "orders_temp"',
                $columnList,
            ));

            Schema::drop('orders_temp');
        });

        $this->recreateSqliteIndexes('orders', $indexes);
    }

    private function rebuildSqliteOrderItemsTable(): void
    {
        $columns = $this->sqliteColumnNames('order_items');

        if ($columns->isEmpty()) {
            return;
        }

        $createSql = $this->sqliteCreateStatement('order_items');

        if ($createSql === null) {
            return;
        }

        $updatedSql = $this->ensureSqliteOrderItemsCreateSqlHasCascade($createSql);

        $needsRebuild = $updatedSql !== $createSql || ! $this->sqliteOrderItemsForeignKeysAreValid();

        if (! $needsRebuild) {
            return;
        }

        $indexes = $this->sqliteIndexesWithDetails('order_items');

        if (Schema::hasTable('order_items_temp')) {
            Schema::drop('order_items_temp');
        }

        DB::transaction(function () use ($columns, $updatedSql): void {
            DB::statement('ALTER TABLE "order_items" RENAME TO "order_items_temp"');
            DB::statement($updatedSql);

            $columnList = $columns
                ->map(static fn (string $column): string => '"' . $column . '"')
                ->implode(', ');

            DB::statement(sprintf(
                'INSERT INTO "order_items" (%1$s) SELECT %1$s FROM "order_items_temp"',
                $columnList,
            ));

            Schema::drop('order_items_temp');
        });

        $this->recreateSqliteIndexes('order_items', $indexes);
    }

    private function ensureSqliteOrderCreateSqlHasUserFk(string $createSql): string
    {
        $pattern = '/foreign\\s+key\\(\\s*"user_id"\\s*\\)\\s*references\\s*"users"\\s*\\(\\s*"id"\\s*\\)(?:\\s+on\\s+delete\\s+\\w+)?/i';

        if (preg_match($pattern, $createSql, $matches) === 1) {
            $clause = $matches[0];

            if (stripos($clause, 'ON DELETE SET NULL') !== false) {
                return $createSql;
            }

            if (stripos($clause, 'ON DELETE') !== false) {
                $replacement = preg_replace('/on\\s+delete\\s+\\w+/i', 'ON DELETE SET NULL', $clause);
            } else {
                $replacement = $clause . ' ON DELETE SET NULL';
            }

            if (is_string($replacement) && $replacement !== '') {
                return str_replace($clause, $replacement, $createSql);
            }

            return $createSql;
        }

        $insertionPoint = strrpos($createSql, ')');

        if ($insertionPoint === false) {
            return $createSql;
        }

        $before = substr($createSql, 0, $insertionPoint);
        $after = substr($createSql, $insertionPoint);

        $before = rtrim($before);

        if (! str_ends_with($before, ',')) {
            $before .= ',';
        }

        $before .= "\n    FOREIGN KEY(\"user_id\") REFERENCES \"users\"(\"id\") ON DELETE SET NULL";

        return $before . "\n" . $after;
    }

    private function ensureSqliteOrderItemsCreateSqlHasCascade(string $createSql): string
    {
        $createSql = $this->ensureSqliteCascadeClause($createSql, 'order_id', 'orders');
        $createSql = $this->ensureSqliteCascadeClause($createSql, 'product_id', 'products');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $createSql = $this->ensureSqliteCascadeClause($createSql, 'product_variant_id', 'product_variants');
        }

        return $createSql;
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

            if (stripos($clause, 'ON DELETE') !== false) {
                $replacement = preg_replace('/on\\s+delete\\s+\\w+/i', 'ON DELETE CASCADE', $clause);
            } else {
                $replacement = $clause . ' ON DELETE CASCADE';
            }

            if (is_string($replacement) && $replacement !== '') {
                return str_replace($clause, $replacement, $createSql);
            }

            return $createSql;
        }

        $insertionPoint = strrpos($createSql, ')');

        if ($insertionPoint === false) {
            return $createSql;
        }

        $before = substr($createSql, 0, $insertionPoint);
        $after = substr($createSql, $insertionPoint);

        $before = rtrim($before);

        if (! str_ends_with($before, ',')) {
            $before .= ',';
        }

        $before .= sprintf(
            "\n    FOREIGN KEY(\"%s\") REFERENCES \"%s\"(\"id\") ON DELETE CASCADE",
            $column,
            $referencedTable,
        );

        return $before . "\n" . $after;
    }

    private function recreateSqliteIndexes(string $table, Collection $indexes): void
    {
        foreach ($indexes as $index) {
            $name = $index['name'] ?? null;
            $columns = $index['columns'] ?? [];

            if (! is_string($name) || $name === '' || $columns === []) {
                continue;
            }

            $unique = ! empty($index['unique']);
            $quotedColumns = implode('", "', $columns);

            DB::statement(sprintf(
                'CREATE %sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")',
                $unique ? 'UNIQUE ' : '',
                $name,
                $table,
                $quotedColumns,
            ));
        }
    }

    private function sqliteForeignKeys(string $table): Collection
    {
        return collect(DB::select("PRAGMA foreign_key_list('{$table}')"))
            ->map(static fn ($foreignKey): array => [
                'from' => $foreignKey->from ?? $foreignKey['from'] ?? null,
                'table' => $foreignKey->table ?? $foreignKey['table'] ?? null,
                'to' => $foreignKey->to ?? $foreignKey['to'] ?? null,
                'on_delete' => strtoupper((string) ($foreignKey->on_delete ?? $foreignKey['on_delete'] ?? '')),
            ]);
    }

    private function sqliteIndexes(string $table): Collection
    {
        return collect(DB::select("PRAGMA index_list('{$table}')"))
            ->map(function ($index) use ($table): array {
                $name = $index->name ?? $index['name'] ?? null;

                if (! is_string($name) || $name === '') {
                    return ['columns' => []];
                }

                $details = DB::select("PRAGMA index_info('{$name}')");

                return [
                    'columns' => array_values(array_filter(array_map(
                        static fn ($detail) => $detail->name ?? $detail['name'] ?? null,
                        $details,
                    ))),
                ];
            });
    }

    private function sqliteIndexesWithDetails(string $table): Collection
    {
        return collect(DB::select("PRAGMA index_list('{$table}')"))
            ->map(function ($index) use ($table): array {
                $name = $index->name ?? $index['name'] ?? null;

                if (! is_string($name) || $name === '') {
                    return [
                        'name' => null,
                        'columns' => [],
                        'unique' => false,
                    ];
                }

                $details = DB::select("PRAGMA index_info('{$name}')");

                return [
                    'name' => $name,
                    'columns' => array_values(array_filter(array_map(
                        static fn ($detail) => $detail->name ?? $detail['name'] ?? null,
                        $details,
                    ))),
                    'unique' => (bool) ($index->unique ?? $index['unique'] ?? false),
                ];
            })
            ->filter(fn (array $index): bool => is_string($index['name']) && $index['name'] !== '' && $index['columns'] !== []);
    }

    private function sqliteColumnNames(string $table): Collection
    {
        return collect(DB::select("PRAGMA table_info('{$table}')"))
            ->map(static fn ($column): ?string => $column->name ?? $column['name'] ?? null)
            ->filter(static fn (?string $name): bool => is_string($name) && $name !== '')
            ->values();
    }

    private function sqliteCreateStatement(string $table): ?string
    {
        $statement = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table]);

        $sql = $statement->sql ?? null;

        return is_string($sql) && $sql !== '' ? $sql : null;
    }

    private function createSqliteIndexIfMissing(string $name, string $table, array $columns, bool $unique = false): void
    {
        $exists = $this->sqliteIndexes($table)
            ->contains(static fn (array $index): bool => $index['columns'] === $columns);

        if ($exists) {
            return;
        }

        $quotedColumns = implode('", "', $columns);

        DB::statement(sprintf(
            'CREATE %sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")',
            $unique ? 'UNIQUE ' : '',
            $name,
            $table,
            $quotedColumns,
        ));
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
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = ?
  AND TABLE_NAME = ?
GROUP BY INDEX_NAME
SQL,
            [$database, $table],
        );

        return collect($indexes)
            ->map(fn ($index) => array_map('trim', explode(',', (string) ($index->indexed_columns ?? ''))))
            ->contains(static fn (array $indexedColumns): bool => $indexedColumns === $columns);
    }

    private function mysqlForeignKeyMetadata(string $table, string $column): ?object
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::selectOne(
            <<<'SQL'
SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS rc
JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
    AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
WHERE rc.CONSTRAINT_SCHEMA = ?
  AND kcu.TABLE_NAME = ?
  AND kcu.COLUMN_NAME = ?
LIMIT 1
SQL,
            [$database, $table, $column],
        );
    }

    private function addMysqlOrderUserForeignKey(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    private function ensureMysqlOrderItemForeignKey(string $column, string $referencedTable): void
    {
        $metadata = $this->mysqlForeignKeyMetadata('order_items', $column);

        if ($metadata === null) {
            $this->addMysqlOrderItemForeignKey($column, $referencedTable);

            return;
        }

        $deleteRule = strtoupper((string) ($metadata->DELETE_RULE ?? ''));

        if ($deleteRule === 'CASCADE') {
            return;
        }

        $constraintName = $metadata->CONSTRAINT_NAME ?? null;

        if (is_string($constraintName) && $constraintName !== '') {
            Schema::table('order_items', function (Blueprint $table) use ($constraintName): void {
                $table->dropForeign($constraintName);
            });
        }

        $this->addMysqlOrderItemForeignKey($column, $referencedTable);
    }

    private function addMysqlOrderItemForeignKey(string $column, string $referencedTable): void
    {
        Schema::table('order_items', function (Blueprint $table) use ($column, $referencedTable): void {
            if (Schema::hasColumn('order_items', $column)) {
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            }
        });
    }
};
