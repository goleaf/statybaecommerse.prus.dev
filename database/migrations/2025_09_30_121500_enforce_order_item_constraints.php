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
        if (! Schema::hasTable('order_items')) {
            return;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $this->enforceSqliteConstraints();

            return;
        }

        if ($driver === 'mysql') {
            $this->enforceMysqlConstraints();

            return;
        }

        $this->ensurePortableIndexes();
    }

    public function down(): void
    {
        throw new RuntimeException('This migration is forward-only and cannot be rolled back.');
    }

    private function enforceSqliteConstraints(): void
    {
        $schema = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'order_items'");

        if ($schema === null) {
            return;
        }

        $originalSql = (string) ($schema->sql ?? '');

        if ($originalSql === '') {
            return;
        }

        $updatedSql = $this->augmentSqliteSchemaWithConstraints($originalSql);

        $needsRebuild = $updatedSql !== $originalSql || $this->sqliteForeignKeysNeedCascade();

        if ($needsRebuild) {
            $this->rebuildSqliteTable($updatedSql);
        }

        $this->ensurePortableIndexes();
    }

    private function enforceMysqlConstraints(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->ensureMysqlForeignKey('order_id', 'orders');
        $this->ensureMysqlForeignKey('product_id', 'products');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $this->ensureMysqlForeignKey('product_variant_id', 'product_variants');
        }

        Schema::enableForeignKeyConstraints();

        $this->ensurePortableIndexes();
    }

    private function ensurePortableIndexes(): void
    {
        $columns = collect(['order_id', 'product_id']);

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $columns = $columns->push('product_variant_id');
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        foreach ($columns as $column) {
            $indexName = sprintf('order_items_%s_index', $column);

            if ($driver === 'sqlite') {
                DB::statement(sprintf(
                    'CREATE INDEX IF NOT EXISTS "%s" ON "order_items" ("%s")',
                    $indexName,
                    $column,
                ));

                continue;
            }

            if ($driver === 'mysql') {
                if (! $this->mysqlIndexExists($indexName)) {
                    Schema::table('order_items', function (Blueprint $table) use ($column, $indexName): void {
                        $table->index($column, $indexName);
                    });
                }

                continue;
            }

            Schema::table('order_items', function (Blueprint $table) use ($column, $indexName): void {
                $table->index($column, $indexName);
            });
        }
    }

    private function augmentSqliteSchemaWithConstraints(string $schemaSql): string
    {
        $schemaSql = $this->ensureCascadeClause($schemaSql, 'order_id', 'orders');
        $schemaSql = $this->ensureCascadeClause($schemaSql, 'product_id', 'products');

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $schemaSql = $this->ensureCascadeClause($schemaSql, 'product_variant_id', 'product_variants');
        }

        return $schemaSql;
    }

    private function ensureCascadeClause(string $schemaSql, string $column, string $referencedTable): string
    {
        $pattern = sprintf(
            '/foreign key\(\s*"%1$s"\s*\)\s*references\s*"%2$s"\s*\(\s*"id"\s*\)(?:\s+on\s+delete\s+\w+)?/i',
            preg_quote($column, '/'),
            preg_quote($referencedTable, '/'),
        );

        if (preg_match($pattern, $schemaSql, $matches) === 1) {
            $clause = $matches[0];

            if (stripos($clause, 'on delete cascade') !== false) {
                return $schemaSql;
            }

            if (stripos($clause, 'on delete') !== false) {
                $replacement = preg_replace('/on\s+delete\s+\w+/i', 'ON DELETE CASCADE', $clause);
            } else {
                $replacement = $clause . ' ON DELETE CASCADE';
            }

            if (is_string($replacement) && $replacement !== '') {
                return str_replace($clause, $replacement, $schemaSql);
            }

            return $schemaSql;
        }

        $insertionPoint = strrpos($schemaSql, ')');

        if ($insertionPoint === false) {
            return $schemaSql;
        }

        $before = substr($schemaSql, 0, $insertionPoint);
        $after = substr($schemaSql, $insertionPoint);

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

    private function sqliteForeignKeysNeedCascade(): bool
    {
        $foreignKeys = collect(DB::select("PRAGMA foreign_key_list('order_items')"));

        $expected = collect([
            ['column' => 'order_id', 'table' => 'orders'],
            ['column' => 'product_id', 'table' => 'products'],
        ]);

        if (Schema::hasColumn('order_items', 'product_variant_id')) {
            $expected = $expected->push(['column' => 'product_variant_id', 'table' => 'product_variants']);
        }

        foreach ($expected as $requirement) {
            $match = $foreignKeys->first(function ($foreignKey) use ($requirement): bool {
                $referencesTable = $foreignKey->table ?? $foreignKey['table'] ?? null;
                $fromColumn = $foreignKey->from ?? $foreignKey['from'] ?? null;
                $onDelete = strtoupper($foreignKey->on_delete ?? $foreignKey['on_delete'] ?? '');

                return $referencesTable === $requirement['table']
                    && $fromColumn === $requirement['column']
                    && $onDelete === 'CASCADE';
            });

            if ($match === null) {
                return true;
            }
        }

        return false;
    }

    private function rebuildSqliteTable(string $createSql): void
    {
        $columnNames = $this->sqliteColumnNames();

        if ($columnNames->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($createSql, $columnNames): void {
            $this->dropSqliteIndexes();

            DB::statement('ALTER TABLE "order_items" RENAME TO "order_items_temp"');

            DB::statement($createSql);

            $columnsList = $columnNames
                ->map(static fn (string $column): string => '"' . $column . '"')
                ->implode(', ');

            DB::statement(sprintf(
                'INSERT INTO "order_items" (%1$s) SELECT %1$s FROM "order_items_temp"',
                $columnsList,
            ));

            Schema::drop('order_items_temp');
        });
    }

    /**
     * @return Collection<int, string>
     */
    private function sqliteColumnNames(): Collection
    {
        return collect(DB::select("PRAGMA table_info('order_items')"))
            ->map(static fn ($column): ?string => $column->name ?? $column['name'] ?? null)
            ->filter(fn ($name): bool => is_string($name) && $name !== '')
            ->values();
    }

    private function dropSqliteIndexes(): void
    {
        $indexes = DB::select("PRAGMA index_list('order_items')");

        foreach ($indexes as $index) {
            $name = $index->name ?? $index['name'] ?? null;

            if (is_string($name) && $name !== '') {
                DB::statement(sprintf('DROP INDEX IF EXISTS "%s"', $name));
            }
        }
    }

    private function ensureMysqlForeignKey(string $column, string $referencedTable): void
    {
        $metadata = $this->mysqlForeignKeyMetadata($column);

        $deleteRule = strtoupper((string) ($metadata->DELETE_RULE ?? ''));

        if ($metadata === null) {
            Schema::table('order_items', function (Blueprint $table) use ($column, $referencedTable): void {
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            });

            return;
        }

        if ($deleteRule === 'CASCADE') {
            return;
        }

        $constraintName = $metadata->CONSTRAINT_NAME ?? null;

        if (is_string($constraintName) && $constraintName !== '') {
            Schema::table('order_items', function (Blueprint $table) use ($constraintName): void {
                $table->dropForeign($constraintName);
            });
        }

        Schema::table('order_items', function (Blueprint $table) use ($column, $referencedTable): void {
            $table->foreign($column)
                ->references('id')
                ->on($referencedTable)
                ->cascadeOnDelete();
        });
    }

    private function mysqlForeignKeyMetadata(string $column): ?object
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
  AND kcu.TABLE_NAME = 'order_items'
  AND kcu.COLUMN_NAME = ?
LIMIT 1
SQL,
            [$database, $column],
        );
    }

    private function mysqlIndexExists(string $indexName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        $result = DB::selectOne(
            "SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'order_items' AND INDEX_NAME = ? LIMIT 1",
            [$database, $indexName],
        );

        return $result !== null;
    }
};
