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

        $snapshot = $this->gatherIndexSnapshot($connection);
        $duplicates = $this->findDuplicateIndexes($snapshot);
        $suggestions = $this->recommendCompositeIndexes($connection, $snapshot);

        if ($this->option('json')) {
            $this->outputJson([
                'duplicates'  => $duplicates,
                'suggestions' => $suggestions,
            ]);

            return $duplicates === [] && $suggestions === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($duplicates === [] && $suggestions === []) {
            $this->components->info('No duplicate indexes found and all recommended composites are present.');

            return self::SUCCESS;
        }

        if ($duplicates === []) {
            $this->components->info('No duplicate indexes found.');
        } else {
            $this->components->error('Duplicate indexes detected:');

            foreach ($duplicates as $duplicate) {
                $indexList = implode(', ', $duplicate['indexes']);
                $columns = implode(', ', $duplicate['columns']);
                $this->line(sprintf('- %s on [%s] (unique: %s) via [%s]', $duplicate['table'], $columns, $duplicate['unique'] ? 'yes' : 'no', $indexList));
            }
        }

        if ($suggestions === []) {
            $this->components->info('No additional composite indexes recommended for commerce tables.');
        } else {
            $this->outputSuggestions($suggestions);
        }

        return $duplicates === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<string, array<string, array{columns:list<string>, unique:bool}>>             $snapshot
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function findDuplicateIndexes(array $snapshot): array
    {
        $duplicates = [];

        foreach ($snapshot as $table => $indexes) {
            $signatures = [];

            foreach ($indexes as $name => $definition) {
                $signature = $this->signature($definition['columns'], $definition['unique']);

                if (isset($signatures[$signature])) {
                    $signatures[$signature]['indexes'][] = $name;
                } else {
                    $signatures[$signature] = [
                        'table'   => $table,
                        'columns' => $definition['columns'],
                        'unique'  => $definition['unique'],
                        'indexes' => [$name],
                    ];
                }
            }

            foreach ($signatures as $candidate) {
                if (count($candidate['indexes']) > 1) {
                    $duplicates[] = $candidate;
                }
            }
        }

        return $duplicates;
    }

    /**
     * @return array<string, array<string, array{columns:list<string>, unique:bool}>>
     */
    private function gatherIndexSnapshot(Connection $connection): array
    {
        return match ($connection->getDriverName()) {
            'sqlite' => $this->snapshotSqlite($connection),
            'mysql', 'mariadb' => $this->snapshotMysql($connection),
            default => $this->snapshotViaDoctrine($connection),
        };
    }

    /**
     * @return array<string, array<string, array{columns:list<string>, unique:bool}>>
     */
    private function snapshotSqlite(Connection $connection): array
    {
        $tableRows = $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");
        $tables = [];

        foreach ($tableRows as $tableRow) {
            if (! $tableRow instanceof stdClass) {
                continue;
            }

            $name = (string) ($tableRow->name ?? '');

            if ($name !== '') {
                $tables[] = $name;
            }
        }

        $snapshot = [];

        foreach ($tables as $table) {
            $snapshot[$table] ??= [];

            $indexRows = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($indexRows as $row) {
                if (! $row instanceof stdClass) {
                    continue;
                }

                $data = (array) $row;
                $indexName = isset($data['name']) ? (string) $data['name'] : '';
                $origin = isset($data['origin']) ? (string) $data['origin'] : '';

                if ($indexName === '' || $origin === 'pk') {
                    continue;
                }

                $columnRows = $connection->select("PRAGMA index_info('{$indexName}')");
                $columnMap = [];

                foreach ($columnRows as $column) {
                    if (! $column instanceof stdClass) {
                        continue;
                    }

                    $columnData = (array) $column;
                    $columnName = isset($columnData['name']) ? (string) $columnData['name'] : '';
                    $sequence = isset($columnData['seqno']) ? (int) $columnData['seqno'] : null;

                    if ($columnName === '' || $sequence === null) {
                        continue;
                    }

                    $columnMap[$sequence] = $columnName;
                }

                if ($columnMap === []) {
                    continue;
                }

                ksort($columnMap);

                /** @var list<string> $columns */
                $columns = array_values($columnMap);

                $snapshot[$table][$indexName] = [
                    'columns' => $columns,
                    'unique'  => (bool) ($data['unique'] ?? false),
                ];
            }
        }

        return $snapshot;
    }

    /**
     * @return array<string, array<string, array{columns:list<string>, unique:bool}>>
     */
    private function snapshotMysql(Connection $connection): array
    {
        $database = (string) $connection->getDatabaseName();

        if ($database === '') {
            return [];
        }

        $rows = $connection->select(
            'SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE '
            . 'FROM information_schema.STATISTICS '
            . 'WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
            [$database]
        );

        $grouped = [];

        foreach ($rows as $row) {
            if (! $row instanceof stdClass) {
                continue;
            }

            $data = (array) $row;
            $table = isset($data['TABLE_NAME']) ? (string) $data['TABLE_NAME'] : '';
            $indexName = isset($data['INDEX_NAME']) ? (string) $data['INDEX_NAME'] : '';

            if ($table === '' || $indexName === '') {
                continue;
            }

            $grouped[$table][$indexName][] = $data;
        }

        $snapshot = [];

        foreach ($grouped as $table => $indexes) {
            foreach ($indexes as $indexName => $rowsForIndex) {
                usort($rowsForIndex, static function (array $a, array $b): int {
                    return ((int) ($a['SEQ_IN_INDEX'] ?? 0)) <=> ((int) ($b['SEQ_IN_INDEX'] ?? 0));
                });

                $columns = [];

                foreach ($rowsForIndex as $rowData) {
                    $columnValue = $rowData['COLUMN_NAME'] ?? null;

                    if (! is_string($columnValue) || $columnValue === '') {
                        continue;
                    }

                    $columns[] = $columnValue;
                }

                if ($columns === []) {
                    continue;
                }

                $firstRow = $rowsForIndex[0];
                $nonUnique = 1;

                if (array_key_exists('NON_UNIQUE', $firstRow) && is_numeric($firstRow['NON_UNIQUE'])) {
                    $nonUnique = (int) $firstRow['NON_UNIQUE'];
                }

                $snapshot[$table][$indexName] = [
                    'columns' => $columns,
                    'unique'  => $nonUnique === 0,
                ];
            }
        }

        return $snapshot;
    }

    /**
     * @return array<string, array<string, array{columns:list<string>, unique:bool}>>
     */
    private function snapshotViaDoctrine(Connection $connection): array
    {
        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            return [];
        }

        $schemaManager = $connection->getDoctrineSchemaManager();

        if (! is_object($schemaManager) || ! method_exists($schemaManager, 'listTableNames')) {
            return [];
        }

        $snapshot = [];
        $tableNames = $schemaManager->listTableNames();

        if (! is_array($tableNames)) {
            return [];
        }

        foreach ($tableNames as $table) {
            if (! is_string($table) || $table === '') {
                continue;
            }

            $snapshot[$table] ??= [];

            if (! method_exists($schemaManager, 'listTableIndexes')) {
                continue;
            }

            $indexes = $schemaManager->listTableIndexes($table);

            if (! is_array($indexes)) {
                continue;
            }

            foreach ($indexes as $index) {
                if (! is_object($index)
                    || ! method_exists($index, 'getColumns')
                    || ! method_exists($index, 'getName')
                    || ! method_exists($index, 'isUnique')) {
                    continue;
                }

                $columns = $index->getColumns();

                if (! is_array($columns) || $columns === []) {
                    continue;
                }

                $columnList = [];

                foreach ($columns as $column) {
                    if (! is_string($column) || $column === '') {
                        continue;
                    }

                    $columnList[] = $column;
                }

                if ($columnList === []) {
                    continue;
                }

                $indexName = $index->getName();

                if (! is_string($indexName) || $indexName === '') {
                    continue;
                }

                $snapshot[$table][$indexName] = [
                    'columns' => $columnList,
                    'unique'  => (bool) $index->isUnique(),
                ];
            }
        }

        return $snapshot;
    }

    /**
     * @param  array<string, array<string, array{columns:list<string>, unique:bool}>> $snapshot
     * @return list<array{table:string, columns:list<string>, description:string}>
     */
    private function recommendCompositeIndexes(Connection $connection, array $snapshot): array
    {
        $suggestions = [];

        foreach ($this->commerceIndexRecommendations() as $table => $recommendations) {
            /** @var array<string, array{columns:list<string>, unique:bool}> $existing */
            $existing = $snapshot[$table] ?? [];

            foreach ($recommendations as $recommendation) {
                $columns = $recommendation['columns'];

                $alreadyCovered = false;

                foreach ($existing as $definition) {
                    $existingColumns = $definition['columns'];

                    if ($existingColumns === $columns) {
                        $alreadyCovered = true;
                        break;
                    }

                    if (count($existingColumns) >= count($columns)
                        && array_slice($existingColumns, 0, count($columns)) === $columns) {
                        $alreadyCovered = true;
                        break;
                    }
                }

                if ($alreadyCovered) {
                    continue;
                }

                $suggestions[] = [
                    'table'       => $table,
                    'columns'     => $columns,
                    'description' => $recommendation['description'],
                ];
            }
        }

        return $suggestions;
    }

    /**
     * @param list<array{table:string, columns:list<string>, description:string}> $suggestions
     */
    private function outputSuggestions(array $suggestions): void
    {
        $this->components->warn('Suggested composite indexes for commerce tables:');

        foreach ($suggestions as $suggestion) {
            $this->line(sprintf('- %s on [%s]: %s', $suggestion['table'], implode(', ', $suggestion['columns']), $suggestion['description']));
        }
    }

    /**
     * Determine if the given table exists on the connection.
     */
    private function tableExists(Connection $connection, string $table): bool
    {
        return $connection->getSchemaBuilder()->hasTable($table);
    }

    /**
     * @return array<string, list<array{columns:list<string>, description:string}>>
     */
    private function commerceIndexRecommendations(): array
    {
        return [
            'orders' => [
                [
                    'columns'     => ['customer_id', 'status'],
                    'description' => "Speed up account timelines that filter a customer's orders by status.",
                ],
                [
                    'columns'     => ['status', 'created_at'],
                    'description' => 'Support admin dashboards that slice order throughput by status and date.',
                ],
            ],
            'order_items' => [
                [
                    'columns'     => ['order_id', 'product_id'],
                    'description' => 'Accelerate fulfillment jobs that join order items with product catalogs.',
                ],
            ],
            'cart_items' => [
                [
                    'columns'     => ['cart_id', 'product_id'],
                    'description' => 'Prevent duplicate cart rows and speed up cart hydration queries.',
                ],
            ],
        ];
    }

    /**
     * @param list<string> $columns
     */
    private function signature(array $columns, bool $isUnique): string
    {
        return implode('|', $columns) . '|unique:' . ($isUnique ? '1' : '0');
    }

    /**
     * @param array{duplicates:list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>, suggestions:list<array{table:string, columns:list<string>, description:string}>} $payload
     */
    private function outputJson(array $payload): void
    {
        $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
