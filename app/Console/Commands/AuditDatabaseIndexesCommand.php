<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

/**
 * Inspect the database schema for duplicate indexes.
 */
final class AuditDatabaseIndexesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'db:audit-indexes {--database=} {--json : Output results as JSON}';

    /**
     * @var string
     */
    protected $description = 'Detect duplicate indexes defined on database tables and suggest missing composites for commerce flows.';

    /**
     * @var array<string, list<array{columns:list<string>, name:string, unique:bool, reason:string}>>
     */
    private const RECOMMENDED_COMPOSITE_INDEXES = [
        'orders' => [
            [
                'columns' => ['status', 'created_at'],
                'name'    => 'index_orders_status_created_at',
                'unique'  => false,
                'reason'  => 'Speeds up order analytics by filtering status windows in dashboards.',
            ],
            [
                'columns' => ['customer_id', 'created_at'],
                'name'    => 'index_orders_customer_created_at',
                'unique'  => false,
                'reason'  => 'Supports recent purchase lookups on the storefront and CRM workflows.',
            ],
        ],
        'order_items' => [
            [
                'columns' => ['order_id', 'product_id'],
                'name'    => 'order_items_order_product_idx',
                'unique'  => false,
                'reason'  => 'Eliminates table scans when expanding orders with their line items.',
            ],
        ],
        'products' => [
            [
                'columns' => ['is_visible', 'price'],
                'name'    => 'products_visibility_price_idx',
                'unique'  => false,
                'reason'  => 'Keeps price range filters and merchandising widgets responsive.',
            ],
            [
                'columns' => ['category_id', 'is_visible'],
                'name'    => 'products_category_visibility_idx',
                'unique'  => false,
                'reason'  => 'Accelerates category listings for visible products in the storefront.',
            ],
        ],
    ];

    public function handle(): int
    {
        $connectionOption = $this->option('database');
        $defaultConnection = config('database.default');

        if (! is_string($defaultConnection) || $defaultConnection === '') {
            $defaultConnection = DB::getDefaultConnection();
        }

        $connectionName = is_string($connectionOption) && $connectionOption !== ''
            ? $connectionOption
            : $defaultConnection;

        /** @var Connection $connection */
        $connection = DB::connection($connectionName);

        // Gather normalized index metadata so both duplicate detection and suggestions operate on a single source of truth.
        /** @var list<array{table:string, name:string, columns:list<string>, unique:bool}> $indexes */
        $indexes = $this->listIndexes($connection);
        $duplicates = $this->findDuplicateIndexes($indexes);
        $suggestions = $this->suggestCompositeIndexes($indexes);

        if ($this->option('json')) {
            $this->outputJson($duplicates, $suggestions);

            return $duplicates === [] && $suggestions === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($duplicates !== []) {
            $this->components->error('Duplicate indexes detected:');

            foreach ($duplicates as $duplicate) {
                $indexList = implode(', ', $duplicate['indexes']);
                $columns = implode(', ', $duplicate['columns']);
                $this->line(sprintf('- %s on [%s] (unique: %s) via [%s]', $duplicate['table'], $columns, $duplicate['unique'] ? 'yes' : 'no', $indexList));
            }
        }

        if ($suggestions !== []) {
            $this->components->warn('Suggested composite indexes for commerce hotspots:');

            foreach ($suggestions as $suggestion) {
                $columns = implode(', ', $suggestion['columns']);
                $this->line(sprintf('- %s: add [%s] (recommended name: %s) → %s', $suggestion['table'], $columns, $suggestion['name'], $suggestion['reason']));
            }
        }

        if ($duplicates === [] && $suggestions === []) {
            $this->components->info('No duplicate indexes found and all recommended composites are present.');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * @return list<array{table:string, name:string, columns:list<string>, unique:bool}>
     */
    private function listIndexes(Connection $connection): array
    {
        return match ($connection->getDriverName()) {
            'sqlite' => $this->listSqliteIndexes($connection),
            'mysql', 'mariadb' => $this->listMysqlIndexes($connection),
            default => $this->listDoctrineIndexes($connection),
        };
    }

    /**
     * @param  list<array{table:string, name:string, columns:list<string>, unique:bool}>          $indexes
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function findDuplicateIndexes(array $indexes): array
    {
        $grouped = [];

        foreach ($indexes as $index) {
            // Build a signature so we can group indexes with identical coverage and uniqueness.
            $signature = $index['table'] . '|' . implode('|', $index['columns']) . '|unique:' . ($index['unique'] ? '1' : '0');

            if (! isset($grouped[$signature])) {
                $grouped[$signature] = [
                    'table'   => $index['table'],
                    'columns' => $index['columns'],
                    'unique'  => $index['unique'],
                    'indexes' => [$index['name']],
                ];
            } else {
                $grouped[$signature]['indexes'][] = $index['name'];
            }
        }

        $duplicates = [];

        foreach ($grouped as $payload) {
            if (count($payload['indexes']) > 1) {
                $duplicates[] = $payload;
            }
        }

        return $duplicates;
    }

    /**
     * @return list<array{table:string, name:string, columns:list<string>, unique:bool}>
     */
    private function listSqliteIndexes(Connection $connection): array
    {
        $indexes = [];
        $tableResults = $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tableResults as $tableResult) {
            if (! $tableResult instanceof stdClass) {
                continue;
            }

            $table = (string) $tableResult->name;

            if ($table === '') {
                continue;
            }

            $indexResults = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($indexResults as $indexResult) {
                if (! $indexResult instanceof stdClass) {
                    continue;
                }

                $row = (array) $indexResult;
                $indexName = isset($row['name']) ? (string) $row['name'] : '';
                $origin = isset($row['origin']) ? (string) $row['origin'] : '';

                if ($indexName === '' || $origin === 'pk') {
                    continue;
                }

                $columnResults = $connection->select("PRAGMA index_info('{$indexName}')");
                $columns = [];

                foreach ($columnResults as $columnResult) {
                    if (! $columnResult instanceof stdClass) {
                        continue;
                    }

                    $column = (array) $columnResult;
                    $columnName = isset($column['name']) ? (string) $column['name'] : '';
                    $sequenceRaw = $column['seqno'] ?? null;
                    $sequence = is_numeric($sequenceRaw) ? (int) $sequenceRaw : 0;

                    if ($columnName === '') {
                        continue;
                    }

                    $columns[$sequence] = $columnName;
                }

                if ($columns === []) {
                    continue;
                }

                ksort($columns);

                /** @var list<string> $orderedColumns */
                $orderedColumns = array_values($columns);

                $isUnique = isset($row['unique']) ? (bool) $row['unique'] : false;

                $indexes[] = [
                    'table'   => $table,
                    'name'    => $indexName,
                    'columns' => $orderedColumns,
                    'unique'  => $isUnique,
                ];
            }
        }

        return $indexes;
    }

    /**
     * @return list<array{table:string, name:string, columns:list<string>, unique:bool}>
     */
    private function listMysqlIndexes(Connection $connection): array
    {
        $database = (string) $connection->getDatabaseName();

        if ($database === '') {
            return [];
        }

        $rawRows = $connection->select(
            'SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE '
            . 'FROM information_schema.STATISTICS '
            . 'WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
            [$database]
        );

        /** @var array<string, array<string, list<array{column:string, seq:int, non_unique:int}>>> $grouped */
        $grouped = [];

        foreach ($rawRows as $rawRow) {
            if (! $rawRow instanceof stdClass) {
                continue;
            }

            $row = (array) $rawRow;
            $table = isset($row['TABLE_NAME']) ? (string) $row['TABLE_NAME'] : '';
            $indexName = isset($row['INDEX_NAME']) ? (string) $row['INDEX_NAME'] : '';

            if ($table === '' || $indexName === '') {
                continue;
            }

            $columnName = isset($row['COLUMN_NAME']) ? (string) $row['COLUMN_NAME'] : '';
            $sequenceRaw = $row['SEQ_IN_INDEX'] ?? null;
            $sequence = is_numeric($sequenceRaw) ? (int) $sequenceRaw : 0;
            $nonUniqueRaw = $row['NON_UNIQUE'] ?? null;
            $nonUnique = is_numeric($nonUniqueRaw) ? (int) $nonUniqueRaw : 1;

            if (! isset($grouped[$table])) {
                $grouped[$table] = [];
            }

            if (! isset($grouped[$table][$indexName])) {
                $grouped[$table][$indexName] = [];
            }

            $grouped[$table][$indexName][] = [
                'column'     => $columnName,
                'seq'        => $sequence,
                'non_unique' => $nonUnique,
            ];
        }

        $indexes = [];

        foreach ($grouped as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName => $parts) {
                $columns = [];
                $isUnique = true;

                foreach ($parts as $part) {
                    $columnName = $part['column'];

                    if ($columnName === '') {
                        continue;
                    }

                    $columns[$part['seq']] = $columnName;
                    $isUnique = $isUnique && $part['non_unique'] === 0;
                }

                if ($columns === []) {
                    continue;
                }

                ksort($columns);

                /** @var list<string> $orderedColumns */
                $orderedColumns = array_values($columns);

                $indexes[] = [
                    'table'   => $table,
                    'name'    => $indexName,
                    'columns' => $orderedColumns,
                    'unique'  => $isUnique,
                ];
            }
        }

        return $indexes;
    }

    /**
     * @return list<array{table:string, name:string, columns:list<string>, unique:bool}>
     */
    private function listDoctrineIndexes(Connection $connection): array
    {
        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            return [];
        }

        $schemaManager = $connection->getDoctrineSchemaManager();

        if (! is_object($schemaManager)) {
            return [];
        }

        if (! method_exists($schemaManager, 'listTableNames') || ! method_exists($schemaManager, 'listTableIndexes')) {
            return [];
        }

        $indexes = [];

        $tableNames = $schemaManager->listTableNames();

        if (! is_iterable($tableNames)) {
            return [];
        }

        foreach ($tableNames as $table) {
            if (! is_string($table) || $table === '') {
                continue;
            }

            $tableName = $table;

            $tableIndexes = $schemaManager->listTableIndexes($tableName);

            if (! is_iterable($tableIndexes)) {
                continue;
            }

            foreach ($tableIndexes as $index) {
                if (! is_object($index)) {
                    continue;
                }

                if (! method_exists($index, 'getColumns') || ! method_exists($index, 'getName') || ! method_exists($index, 'isUnique')) {
                    continue;
                }

                $columns = $index->getColumns();

                if (! is_array($columns) || $columns === []) {
                    continue;
                }

                $columnNames = [];

                foreach ($columns as $column) {
                    if (! is_string($column) || $column === '') {
                        continue;
                    }

                    $columnNames[] = $column;
                }

                if ($columnNames === []) {
                    continue;
                }

                $name = $index->getName();

                if (! is_string($name) || $name === '') {
                    continue;
                }

                $uniqueFlag = $index->isUnique();

                if (! is_bool($uniqueFlag)) {
                    continue;
                }

                $indexes[] = [
                    'table'   => $tableName,
                    'name'    => $name,
                    'columns' => $columnNames,
                    'unique'  => $uniqueFlag,
                ];
            }
        }

        return $indexes;
    }

    /**
     * @param list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>       $duplicates
     * @param list<array{table:string, columns:list<string>, unique:bool, name:string, reason:string}> $suggestions
     */
    private function outputJson(array $duplicates, array $suggestions): void
    {
        $this->line((string) json_encode([
            'duplicates'  => $duplicates,
            'suggestions' => $suggestions,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  list<array{table:string, name:string, columns:list<string>, unique:bool}>                $indexes
     * @return list<array{table:string, columns:list<string>, unique:bool, name:string, reason:string}>
     */
    private function suggestCompositeIndexes(array $indexes): array
    {
        $byTable = [];

        foreach ($indexes as $index) {
            $table = $index['table'];

            if (! isset($byTable[$table])) {
                $byTable[$table] = [];
            }

            $byTable[$table][] = $index;
        }

        $suggestions = [];

        foreach (self::RECOMMENDED_COMPOSITE_INDEXES as $table => $recommendations) {
            $existing = $byTable[$table] ?? [];

            foreach ($recommendations as $recommendation) {
                if ($existing === []) {
                    // Without any indexes present we can immediately recommend the composite.
                    $suggestions[] = [
                        'table'   => $table,
                        'columns' => $recommendation['columns'],
                        'unique'  => $recommendation['unique'],
                        'name'    => $recommendation['name'],
                        'reason'  => $recommendation['reason'],
                    ];

                    continue;
                }

                $alreadyCovered = false;

                foreach ($existing as $existingIndex) {
                    /** @var list<string> $expected */
                    $expected = array_map(
                        static fn (string $column): string => Str::lower($column),
                        $recommendation['columns']
                    );
                    /** @var list<string> $actual */
                    $actual = array_map(
                        static fn (string $column): string => Str::lower($column),
                        $existingIndex['columns']
                    );

                    if ($expected === $actual && $existingIndex['unique'] === $recommendation['unique']) {
                        $alreadyCovered = true;

                        break;
                    }
                }

                if ($alreadyCovered) {
                    continue;
                }

                $suggestions[] = [
                    'table'   => $table,
                    'columns' => $recommendation['columns'],
                    'unique'  => $recommendation['unique'],
                    'name'    => $recommendation['name'],
                    'reason'  => $recommendation['reason'],
                ];
            }
        }

        return $suggestions;
    }
}
