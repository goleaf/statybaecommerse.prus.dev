<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Stringable;

final class DbAuditIndexesCommand extends Command
{
    /**
     * @var array<int, array{table: string, columns: array<int, string>, reason: string, enforce: bool}>
     */
    private const RECOMMENDATIONS = [
        ['table' => 'orders', 'columns' => ['customer_id', 'status'], 'reason' => 'keeps CRM order status dashboards responsive', 'enforce' => false],
        ['table' => 'orders', 'columns' => ['status', 'created_at'], 'reason' => 'accelerates fulfilment queues sorted by status and recency', 'enforce' => true],
        ['table' => 'orders', 'columns' => ['customer_id', 'created_at'], 'reason' => 'supports retention reports per customer with date filters', 'enforce' => true],
        ['table' => 'order_items', 'columns' => ['order_id', 'product_id'], 'reason' => 'optimises joins between orders and ordered products', 'enforce' => true],
        ['table' => 'cart_items', 'columns' => ['cart_id', 'product_id'], 'reason' => 'stabilises storefront cart merges across sessions', 'enforce' => false],
        ['table' => 'products', 'columns' => ['is_visible', 'price'], 'reason' => 'improves merchandising filters combining visibility and price', 'enforce' => true],
        ['table' => 'products', 'columns' => ['category_id', 'is_visible'], 'reason' => 'speeds up catalogue category listings by visibility', 'enforce' => true],
    ];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:audit-indexes {--database= : Connection name to inspect for duplicate and missing indexes}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze database indexes, highlighting duplicates and suggesting improvements';

    /**
     * Connection name configured for this run; null implies the default connection.
     */
    private ?string $connectionName = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Resolve the requested database connection, defaulting to the application's configured default.
        $databaseOption = $this->option('database');
        if (is_string($databaseOption) && $databaseOption !== '') {
            $this->connectionName = $databaseOption;
        }

        try {
            $tables = $this->listTables();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

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
        $this->info('Suggested composite indexes for commerce tables:');

        $suggestions = $this->suggestIndexes($tables);

        foreach (self::RECOMMENDATIONS as $recommendation) {
            $this->line(sprintf(
                '- %s on [%s]: %s',
                $recommendation['table'],
                implode(', ', $recommendation['columns']),
                $recommendation['reason'],
            ));
        }

        if ($suggestions === []) {
            $this->line('- All tracked composite indexes are present.');

            if ($duplicatesFound) {
                return self::FAILURE;
            }

            $this->info('No duplicate indexes found and all recommended composites are present');

            return self::SUCCESS;
        }

        foreach ($suggestions as $suggestion) {
            $this->line(sprintf(
                '- %s on [%s]: %s',
                $suggestion['table'],
                implode(', ', $suggestion['columns']),
                $suggestion['reason'],
            ));
        }

        return self::FAILURE;
    }

    /**
     * @param  array<int, array{name: string, columns: array<int, string>, unique: bool, primary: bool}> $indexes
     * @return array<string, list<string>>
     */
    private function detectDuplicateIndexes(array $indexes): array
    {
        $signatures = [];

        foreach ($indexes as $index) {
            if ($index['primary']) {
                continue;
            }

            $key = implode('|', $index['columns']) . '|' . ($index['unique'] ? 'unique' : 'non_unique');

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
     * @param  list<string>                                                      $tables
     * @return list<array{table: string, columns: list<string>, reason: string}>
     */
    private function suggestIndexes(array $tables): array
    {
        $suggestions = [];

        foreach (self::RECOMMENDATIONS as $recommendation) {
            if (! $recommendation['enforce']) {
                continue;
            }

            if (! in_array($recommendation['table'], $tables, true)) {
                continue;
            }

            $indexes = $this->listIndexes($recommendation['table']);
            $existing = array_map(
                static fn (array $index): array => $index['columns'],
                $indexes,
            );

            if ($this->hasMatchingIndex($existing, $recommendation['columns'])) {
                continue;
            }

            $suggestions[] = [
                'table'   => $recommendation['table'],
                'columns' => $recommendation['columns'],
                'reason'  => $recommendation['reason'],
            ];
        }

        return $suggestions;
    }

    /**
     * @param array<int, array<int, string>> $existing
     * @param list<string>                   $target
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
        $connection = $this->connection();
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
                        if (is_scalar($name) || $name instanceof Stringable) {
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
        $connection = $this->connection();
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
                    'name'    => $indexName,
                    'columns' => array_values(array_filter(array_map(
                        static function (object $col): ?string {
                            $column = (array) $col;

                            return isset($column['name']) ? (string) $column['name'] : null;
                        },
                        $columns,
                    ))),
                    'unique'  => $unique,
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
                    'name'    => $name,
                    'columns' => [],
                    'unique'  => ! (bool) $nonUnique,
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
                    'name'    => $indexName,
                    'columns' => $columns,
                    'unique'  => str_contains(strtoupper($definition), 'UNIQUE'),
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
                        'name'    => $name,
                        'columns' => $index->getColumns(),
                        'unique'  => method_exists($index, 'isUnique') ? $index->isUnique() : false,
                        'primary' => method_exists($index, 'isPrimary') ? $index->isPrimary() : false,
                    ];
                }

                return $normalized;
            }
        }

        return [];
    }

    private function connection(): ConnectionInterface
    {
        // Centralise connection resolution so every helper pulls from the requested database name.
        try {
            return DB::connection($this->connectionName);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
