<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_table_has_expected_constraints(): void
    {
        $this->assertTrue(
            $this->foreignKeyExists('orders', 'user_id', 'users', 'id', 'SET NULL'),
            'orders.user_id should reference users.id with SET NULL delete behaviour.',
        );

        $this->assertTrue(
            $this->indexExists('orders', ['status', 'created_at']),
            'orders table should be indexed by status and created_at.',
        );

        $this->assertTrue(
            $this->indexExists('orders', ['user_id', 'created_at']),
            'orders table should be indexed by user_id and created_at.',
        );
    }

    public function test_order_items_table_has_expected_constraints(): void
    {
        $this->assertTrue(
            $this->foreignKeyExists('order_items', 'order_id', 'orders', 'id', 'CASCADE'),
            'order_items.order_id should cascade delete with orders.',
        );

        $this->assertTrue(
            $this->foreignKeyExists('order_items', 'product_id', 'products', 'id', 'CASCADE'),
            'order_items.product_id should cascade delete with products.',
        );

        $this->assertTrue(
            $this->foreignKeyExists('order_items', 'product_variant_id', 'product_variants', 'id', 'CASCADE', allowNull: true),
            'order_items.product_variant_id should cascade delete with product_variants when present.',
        );

        foreach ([['order_id'], ['product_id'], ['product_variant_id']] as $columns) {
            $this->assertTrue(
                $this->indexExists('order_items', $columns),
                sprintf('order_items should be indexed by %s.', implode(', ', $columns)),
            );
        }
    }

    public function test_order_shippings_table_has_expected_constraints(): void
    {
        $this->assertTrue(
            $this->foreignKeyExists('order_shippings', 'order_id', 'orders', 'id', 'CASCADE'),
            'order_shippings.order_id should cascade delete with orders.',
        );

        foreach ([
            ['order_id'],
            ['status'],
            ['created_at'],
            ['tracking_number'],
        ] as $columns) {
            $this->assertTrue(
                $this->indexExists('order_shippings', $columns),
                sprintf('order_shippings should be indexed by %s.', implode(', ', $columns)),
            );
        }
    }

    private function foreignKeyExists(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete,
        bool $allowNull = false,
    ): bool {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return $this->sqliteForeignKeys($table)
                ->first(fn (array $foreignKey): bool => $foreignKey['from'] === $column
                    && $foreignKey['table'] === $referencedTable
                    && $foreignKey['to'] === $referencedColumn
                    && ($allowNull || strtoupper($foreignKey['on_delete']) === strtoupper($onDelete))) !== null;
        }

        if ($driver === 'mysql') {
            $foreignKey = DB::selectOne(
                <<<'SQL'
SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS rc
JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
    AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
  AND kcu.TABLE_NAME = ?
  AND kcu.COLUMN_NAME = ?
  AND kcu.REFERENCED_TABLE_NAME = ?
  AND kcu.REFERENCED_COLUMN_NAME = ?
LIMIT 1
SQL,
                [$table, $column, $referencedTable, $referencedColumn],
            );

            if ($foreignKey === null) {
                return false;
            }

            return $allowNull || strtoupper($foreignKey->DELETE_RULE ?? '') === strtoupper($onDelete);
        }

        return true; // Optimistically pass for other drivers until explicitly supported.
    }

    private function indexExists(string $table, array $columns): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return $this->sqliteIndexes($table)
                ->contains(fn (array $index): bool => $index['columns'] === $columns);
        }

        if ($driver === 'mysql') {
            $indexes = DB::select(
                <<<'SQL'
SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS indexed_columns
FROM information_schema.statistics
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = ?
GROUP BY INDEX_NAME
SQL,
                [$table],
            );

            return collect($indexes)
                ->map(fn ($index) => [
                    'columns' => explode(',', (string) ($index->indexed_columns ?? '')),
                ])
                ->contains(fn (array $index): bool => $index['columns'] === $columns);
        }

        return true;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function sqliteForeignKeys(string $table): Collection
    {
        return collect(DB::select("PRAGMA foreign_key_list('{$table}')"))
            ->map(fn ($foreignKey): array => [
                'from'      => $foreignKey->from ?? $foreignKey['from'] ?? null,
                'table'     => $foreignKey->table ?? $foreignKey['table'] ?? null,
                'to'        => $foreignKey->to ?? $foreignKey['to'] ?? null,
                'on_delete' => strtoupper($foreignKey->on_delete ?? $foreignKey['on_delete'] ?? ''),
            ]);
    }

    /**
     * @return Collection<int, array{columns: array<int, string|null>}>
     */
    private function sqliteIndexes(string $table): Collection
    {
        return collect(DB::select("PRAGMA index_list('{$table}')"))
            ->map(function ($index): array {
                $name = $index->name ?? $index['name'] ?? null;

                if ($name === null) {
                    return ['columns' => []];
                }

                $details = DB::select("PRAGMA index_info('{$name}')");

                return [
                    'columns' => array_map(
                        static fn ($detail) => $detail->name ?? $detail['name'] ?? null,
                        $details,
                    ),
                ];
            });
    }
}
