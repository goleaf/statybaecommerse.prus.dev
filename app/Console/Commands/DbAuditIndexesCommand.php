<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DbAuditIndexesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:audit-indexes';

    /**
     * The console command description.
     */
    protected $description = 'Analyze database indexes, highlighting duplicates and suggesting improvements';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tables = $this->listTables();
        if ($tables === []) {
            $this->info('No tables found to audit.');

            return self::SUCCESS;
        }

        $this->info('Scanning indexes for duplicate definitions...');
        $duplicatesFound = false;

        foreach ($tables as $table) {
            $indexes = $this->listIndexes($table);
            $duplicates = $this->detectDuplicateIndexes($indexes);

            foreach ($duplicates as $columns => $names) {
                $duplicatesFound = true;
                $this->warn(sprintf(
                    'Duplicate index detected on %s for columns [%s]: %s',
                    $table,
                    $columns,
                    implode(', ', $names),
                ));
            }
        }

        if (! $duplicatesFound) {
            $this->info('No duplicate indexes detected.');
        }

        $this->newLine();
        $this->info('Generating index improvement suggestions...');

        $suggestions = $this->suggestIndexes($tables);

        if ($suggestions === []) {
            $this->info('No missing indexes were identified based on known query patterns.');

            return self::SUCCESS;
        }

        foreach ($suggestions as $suggestion) {
            $this->line(sprintf(
                'Suggestion: add index on %s (%s) - %s',
                $suggestion['table'],
                implode(', ', $suggestion['columns']),
                $suggestion['reason'],
            ));
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>  $indexes
     * @return array<string, list<string>>
     */
    private function detectDuplicateIndexes(array $indexes): array
    {
        $signatures = [];

        foreach ($indexes as $index) {
            if ($index['primary']) {
                continue;
            }

            $key = implode('|', $index['columns']).'|'.($index['unique'] ? 'unique' : 'non_unique');

            $signatures[$key] ??= [];
            $signatures[$key][] = $index['name'];
        }

        $duplicates = [];

        foreach ($signatures as $key => $names) {
            if (count($names) < 2) {
                continue;
            }

            $columns = explode('|', $key);
            array_pop($columns);

            $duplicates[implode(', ', $columns)] = $names;
        }

        return $duplicates;
    }

    /**
     * @param  list<string>  $tables
     * @return list<array{table: string, columns: list<string>, reason: string}>
     */
    private function suggestIndexes(array $tables): array
    {
        $expected = [
            'orders' => [
                ['columns' => ['user_id', 'status'], 'reason' => 'supports frequent customer order status lookups'],
                ['columns' => ['created_at'], 'reason' => 'improves recent orders sorting and filtering'],
            ],
            'order_items' => [
                ['columns' => ['order_id', 'product_id'], 'reason' => 'accelerates order detail joins and product frequency queries'],
            ],
            'products' => [
                ['columns' => ['sku'], 'reason' => 'ensures fast SKU based product retrieval'],
                ['columns' => ['category_id', 'is_active'], 'reason' => 'optimizes merchandising filters by category and status'],
            ],
            'users' => [
                ['columns' => ['email'], 'reason' => 'prevents full table scans for login lookups'],
            ],
            'product_categories' => [
                ['columns' => ['parent_id', 'slug'], 'reason' => 'speeds up navigation tree resolution by parent and slug'],
            ],
        ];

        $suggestions = [];

        foreach ($expected as $table => $configs) {
            if (! in_array($table, $tables, true)) {
                continue;
            }

            $indexes = $this->listIndexes($table);
            $existing = array_map(
                static fn (array $index): array => $index['columns'],
                $indexes,
            );

            foreach ($configs as $config) {
                if ($this->hasMatchingIndex($existing, $config['columns'])) {
                    continue;
                }

                $suggestions[] = [
                    'table' => $table,
                    'columns' => $config['columns'],
                    'reason' => $config['reason'],
                ];
            }
        }

        return $suggestions;
    }

    /**
     * @param  array<int, array<int, string>>  $existing
     * @param  list<string>  $target
     */
    private function hasMatchingIndex(array $existing, array $target): bool
    {
        foreach ($existing as $columns) {
            if (array_slice($columns, 0, count($target)) === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function listTables(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (in_array($driver, ['sqlite', 'sqlite3'], true)) {
            $rows = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            $filtered = array_filter(
                $rows,
                static function (object $row): bool {
                    $data = (array) $row;

                    return isset($data['name']) && is_string($data['name']);
                },
            );

            return array_values(array_map(
                static function (object $row): string {
                    $data = (array) $row;

                    return (string) $data['name'];
                },
                $filtered,
            ));
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $rows = $connection->select("SELECT table_name FROM information_schema.tables WHERE table_schema = database() AND table_type = 'BASE TABLE'");

            $filtered = array_filter(
                $rows,
                static function (object $row): bool {
                    $data = (array) $row;

                    return isset($data['table_name']) && is_string($data['table_name']);
                },
            );

            return array_values(array_map(
                static function (object $row): string {
                    $data = (array) $row;

                    return (string) $data['table_name'];
                },
                $filtered,
            ));
        }

        if ($driver === 'pgsql') {
            $rows = $connection->select('SELECT tablename FROM pg_tables WHERE schemaname = current_schema()');

            $filtered = array_filter(
                $rows,
                static function (object $row): bool {
                    $data = (array) $row;

                    return isset($data['tablename']) && is_string($data['tablename']);
                },
            );

            return array_values(array_map(
                static function (object $row): string {
                    $data = (array) $row;

                    return (string) $data['tablename'];
                },
                $filtered,
            ));
        }

        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            $schemaManager = $connection->getDoctrineSchemaManager();

            if ($schemaManager && is_object($schemaManager) && method_exists($schemaManager, 'listTableNames')) {
                $names = $schemaManager->listTableNames();

                if (! is_array($names)) {
                    return [];
                }

                $stringNames = array_values(array_filter(array_map(
                    static function ($name): ?string {
                        if (is_scalar($name) || $name instanceof \Stringable) {
                            return (string) $name;
                        }

                        return null;
                    },
                    $names,
                )));

                return $stringNames;
            }
        }

        return [];
    }

    /**
     * @return array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}>
     */
    private function listIndexes(string $table): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (in_array($driver, ['sqlite', 'sqlite3'], true)) {
            $indexes = [];
            $rows = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                $data = (array) $row;
                $indexName = isset($data['name']) ? (string) $data['name'] : '';
                $origin = isset($data['origin']) ? (string) $data['origin'] : '';
                $unique = isset($data['unique']) ? (bool) $data['unique'] : false;

                if ($indexName === '') {
                    continue;
                }

                $columns = $connection->select("PRAGMA index_info('{$indexName}')");
                usort($columns, static function (object $a, object $b): int {
                    $first = (array) $a;
                    $second = (array) $b;

                    return ((int) ($first['seqno'] ?? 0)) <=> ((int) ($second['seqno'] ?? 0));
                });

                $indexes[] = [
                    'name' => $indexName,
                    'columns' => array_values(array_filter(array_map(
                        static function (object $col): ?string {
                            $column = (array) $col;

                            return isset($column['name']) ? (string) $column['name'] : null;
                        },
                        $columns,
                    ))),
                    'unique' => $unique,
                    'primary' => $origin === 'pk',
                ];
            }

            return $indexes;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $rows = $connection->select("SHOW INDEX FROM `{$table}`");
            $grouped = [];

            foreach ($rows as $row) {
                $data = (array) $row;
                $name = isset($data['Key_name']) ? (string) $data['Key_name'] : '';
                $sequence = isset($data['Seq_in_index']) ? (int) $data['Seq_in_index'] : 0;
                $columnName = isset($data['Column_name']) ? (string) $data['Column_name'] : '';
                $nonUnique = isset($data['Non_unique']) ? (int) $data['Non_unique'] : 1;

                if ($name === '' || $columnName === '') {
                    continue;
                }

                $grouped[$name] ??= [
                    'name' => $name,
                    'columns' => [],
                    'unique' => ! (bool) $nonUnique,
                    'primary' => $name === 'PRIMARY',
                ];

                $grouped[$name]['columns'][$sequence - 1] = $columnName;
            }

            foreach ($grouped as &$index) {
                ksort($index['columns']);
                $index['columns'] = array_values($index['columns']);
            }

            return array_values($grouped);
        }

        if ($driver === 'pgsql') {
            $rows = $connection->select(
                'SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ?',
                [$table],
            );

            $indexes = [];
            foreach ($rows as $row) {
                $data = (array) $row;
                $definition = isset($data['indexdef']) ? (string) $data['indexdef'] : '';
                $indexName = isset($data['indexname']) ? (string) $data['indexname'] : '';
                $start = strpos($definition, '(');
                $end = strrpos($definition, ')');
                $columns = [];

                if ($start !== false && $end !== false && $end > $start) {
                    $columnList = substr($definition, $start + 1, $end - $start - 1);
                    $columns = array_map(static fn (string $column): string => trim(str_replace('"', '', $column)), explode(',', $columnList));
                }

                $indexes[] = [
                    'name' => $indexName,
                    'columns' => $columns,
                    'unique' => str_contains(strtoupper($definition), 'UNIQUE'),
                    'primary' => str_contains(strtoupper($definition), 'PRIMARY KEY'),
                ];
            }

            return $indexes;
        }

        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            $schemaManager = $connection->getDoctrineSchemaManager();
            if ($schemaManager && is_object($schemaManager) && method_exists($schemaManager, 'listTableIndexes')) {
                $indexes = $schemaManager->listTableIndexes($table);
                $normalized = [];

                foreach ($indexes as $name => $index) {
                    if (! is_object($index) || ! method_exists($index, 'getColumns')) {
                        continue;
                    }

                    $normalized[] = [
                        'name' => $name,
                        'columns' => $index->getColumns(),
                        'unique' => method_exists($index, 'isUnique') ? $index->isUnique() : false,
                        'primary' => method_exists($index, 'isPrimary') ? $index->isPrimary() : false,
                    ];
                }

                return $normalized;
            }
        }

        return [];
    }
}
