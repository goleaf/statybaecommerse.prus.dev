<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
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
    protected $description = 'Detect duplicate indexes defined on database tables.';

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

        $duplicates = $this->findDuplicateIndexes($connection);

        if ($this->option('json')) {
            $this->outputJson($duplicates);

            return $duplicates === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($duplicates === []) {
            $this->components->info('No duplicate indexes found.');

            return self::SUCCESS;
        }

        $this->components->error('Duplicate indexes detected:');

        foreach ($duplicates as $duplicate) {
            $indexList = implode(', ', $duplicate['indexes']);
            $columns = implode(', ', $duplicate['columns']);
            $this->line(sprintf('- %s on [%s] (unique: %s) via [%s]', $duplicate['table'], $columns, $duplicate['unique'] ? 'yes' : 'no', $indexList));
        }

        return self::FAILURE;
    }

    /**
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function findDuplicateIndexes(Connection $connection): array
    {
        return match ($connection->getDriverName()) {
            'sqlite' => $this->scanSqlite($connection),
            'mysql', 'mariadb' => $this->scanMysql($connection),
            default => $this->scanViaDoctrine($connection),
        };
    }

    /**
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function scanSqlite(Connection $connection): array
    {
        $tables = collect($connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->map(static fn (stdClass $row): string => (string) $row->name)
            ->filter();

        $duplicates = [];

        foreach ($tables as $table) {
            $indexRows = collect($connection->select("PRAGMA index_list('{$table}')"))
                ->map(static fn (stdClass $row): array => (array) $row);

            $signatures = [];

            foreach ($indexRows as $row) {
                $indexName = (string) ($row['name'] ?? '');
                $origin = (string) ($row['origin'] ?? '');

                if ($indexName === '' || $origin === 'pk') {
                    continue;
                }

                $columnRows = collect($connection->select("PRAGMA index_info('{$indexName}')"))
                    ->map(static fn (stdClass $column): array => (array) $column)
                    ->sortBy(static fn (array $column): int => (int) ($column['seqno'] ?? 0))
                    ->map(static fn (array $column): string => (string) ($column['name'] ?? ''))
                    ->filter(static fn (string $column): bool => $column !== '')
                    ->all();

                if ($columnRows === []) {
                    continue;
                }

                /** @var list<string> $columnRows */
                $columnRows = array_values($columnRows);

                $isUnique = (bool) ($row['unique'] ?? false);
                $signature = implode('|', $columnRows).'|unique:'.($isUnique ? '1' : '0');

                if (isset($signatures[$signature])) {
                    $signatures[$signature]['indexes'][] = $indexName;
                } else {
                    $signatures[$signature] = [
                        'table' => $table,
                        'columns' => $columnRows,
                        'unique' => $isUnique,
                        'indexes' => [$indexName],
                    ];
                }
            }

            foreach ($signatures as $payload) {
                if (count($payload['indexes']) > 1) {
                    $duplicates[] = [
                        'table' => $payload['table'],
                        'columns' => $payload['columns'],
                        'unique' => $payload['unique'],
                        'indexes' => $payload['indexes'],
                    ];
                }
            }
        }

        return $duplicates;
    }

    /**
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function scanMysql(Connection $connection): array
    {
        $database = (string) $connection->getDatabaseName();

        if ($database === '') {
            return [];
        }

        $rows = collect($connection->select(
            'SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE '
            .'FROM information_schema.STATISTICS '
            .'WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
            [$database]
        ))
            ->map(static fn (stdClass $row): array => (array) $row);

        $grouped = $rows->groupBy(static fn (array $row): string => ($row['TABLE_NAME'] ?? '').'|'.($row['INDEX_NAME'] ?? ''));

        $tableIndexMap = [];

        foreach ($grouped as $key => $group) {
            [$table, $indexName] = explode('|', $key, 2);

            $columns = $group
                ->sortBy(static fn (array $row): int => (int) ($row['SEQ_IN_INDEX'] ?? 0))
                ->map(static fn (array $row): string => (string) ($row['COLUMN_NAME'] ?? ''))
                ->filter(static fn (string $column): bool => $column !== '')
                ->all();

            if ($columns === []) {
                continue;
            }

            /** @var list<string> $columns */
            $columns = array_values($columns);

            $first = $group->first();
            $isUnique = ((int) (($first['NON_UNIQUE'] ?? 1))) === 0;
            $signature = implode('|', $columns).'|unique:'.($isUnique ? '1' : '0');
            $compositeKey = $table.'|'.$signature;

            if (isset($tableIndexMap[$compositeKey])) {
                $tableIndexMap[$compositeKey]['indexes'][] = $indexName;
            } else {
                $tableIndexMap[$compositeKey] = [
                    'table' => $table,
                    'columns' => $columns,
                    'unique' => $isUnique,
                    'indexes' => [$indexName],
                ];
            }
        }

        $duplicates = [];

        foreach ($tableIndexMap as $payload) {
            if (count($payload['indexes']) > 1) {
                $duplicates[] = [
                    'table' => $payload['table'],
                    'columns' => $payload['columns'],
                    'unique' => $payload['unique'],
                    'indexes' => $payload['indexes'],
                ];
            }
        }

        return $duplicates;
    }

    /**
     * @return list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>
     */
    private function scanViaDoctrine(Connection $connection): array
    {
        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            return [];
        }

        $schemaManager = $connection->getDoctrineSchemaManager();
        $duplicates = [];

        foreach ($schemaManager->listTableNames() as $table) {
            $signatures = [];

            foreach ($schemaManager->listTableIndexes($table) as $index) {
                $columns = $index->getColumns();
                if ($columns === []) {
                    continue;
                }

                $isUnique = $index->isUnique();
                $signature = implode('|', $columns).'|unique:'.($isUnique ? '1' : '0');

                if (isset($signatures[$signature])) {
                    $signatures[$signature]['indexes'][] = $index->getName();
                } else {
                    $signatures[$signature] = [
                        'table' => $table,
                        'columns' => $columns,
                        'unique' => $isUnique,
                        'indexes' => [$index->getName()],
                    ];
                }
            }

            foreach ($signatures as $payload) {
                if (count($payload['indexes']) > 1) {
                    $duplicates[] = $payload;
                }
            }
        }

        return $duplicates;
    }

    /**
     * @param  list<array{table:string, columns:list<string>, unique:bool, indexes:list<string>}>  $duplicates
     */
    private function outputJson(array $duplicates): void
    {
        $this->line((string) json_encode($duplicates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
