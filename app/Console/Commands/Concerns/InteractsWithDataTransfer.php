<?php

declare(strict_types=1);

namespace App\Console\Commands\Concerns;

use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;
use RuntimeException;

trait InteractsWithDataTransfer
{
    private const ENTITY_TABLE_MAP = [
        'categories' => 'categories',
        'products'   => 'products',
        'attributes' => 'attributes',
    ];

    private const NULL_PLACEHOLDER = '__NULL__';

    /**
     * @return array<int, string>
     */
    protected function supportedEntities(): array
    {
        return array_keys(self::ENTITY_TABLE_MAP);
    }

    protected function isSupportedEntity(string $entity): bool
    {
        return array_key_exists($entity, self::ENTITY_TABLE_MAP);
    }

    protected function tableFor(string $entity): string
    {
        if (! $this->isSupportedEntity($entity)) {
            throw new RuntimeException("Unsupported entity [{$entity}].");
        }

        return self::ENTITY_TABLE_MAP[$entity];
    }

    protected function resolveFormat(?string $format, ?string $path = null): string
    {
        $format = $format ? strtolower($format) : null;

        if ($format === null && $path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($extension, ['json', 'csv'], true)) {
                $format = $extension;
            }
        }

        if (! in_array($format, ['json', 'csv'], true)) {
            $format = 'json';
        }

        return $format;
    }

    protected function defaultExportPath(string $entity, string $format): string
    {
        return storage_path('app/exports/' . $entity . '-' . now()->format('Ymd-His') . '.' . $format);
    }

    protected function ensureDirectory(string $path): void
    {
        if (str_starts_with($path, 'php://')) {
            return;
        }

        File::ensureDirectoryExists((string) dirname($path));
    }

    /**
     * @param resource $handle
     */
    protected function exportRows(string $format, $handle, string $table): int
    {
        return match ($format) {
            'csv'   => $this->writeCsv($handle, $this->iterateRows($table), $table),
            default => $this->writeJson($handle, $this->iterateRows($table)),
        };
    }

    /**
     * @param resource $handle
     */
    protected function importRows(string $format, $handle, string $table, int $chunkSize): int
    {
        $count = 0;
        $batch = [];

        foreach ($this->readRows($format, $handle) as $row) {
            $batch[] = $row;

            if (count($batch) >= $chunkSize) {
                $this->persistBatch($table, $batch);
                $count += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->persistBatch($table, $batch);
            $count += count($batch);
        }

        return $count;
    }

    /**
     * @return LazyCollection<int, object>
     */
    protected function iterateRows(string $table): LazyCollection
    {
        return DB::table($table)->orderBy('id')->lazy();
    }

    /**
     * @param resource                                   $handle
     * @param iterable<int, array<string, mixed>|object> $rows
     */
    protected function writeJson($handle, iterable $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $encoded = json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($encoded === false) {
                throw new RuntimeException('Unable to encode row as JSON.');
            }

            if (fwrite($handle, $encoded . PHP_EOL) === false) {
                throw new RuntimeException('Unable to write JSON row to output.');
            }

            $count++;
        }

        return $count;
    }

    /**
     * @param resource                                   $handle
     * @param iterable<int, array<string, mixed>|object> $rows
     */
    protected function writeCsv($handle, iterable $rows, string $table): int
    {
        $headers = $this->tableColumns($table);

        if ($headers === []) {
            return 0;
        }

        if (fputcsv($handle, $headers) === false) {
            throw new RuntimeException('Unable to write CSV header.');
        }

        $count = 0;

        foreach ($rows as $row) {
            $data = (array) $row;
            $ordered = [];

            foreach ($headers as $header) {
                $value = $data[$header] ?? null;

                if ($value === null) {
                    $value = self::NULL_PLACEHOLDER;
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif (! is_scalar($value)) {
                    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $value = $encoded === false ? '' : $encoded;
                }

                $ordered[] = $value;
            }

            if (fputcsv($handle, $ordered) === false) {
                throw new RuntimeException('Unable to write CSV row.');
            }

            $count++;
        }

        return $count;
    }

    /**
     * @param  resource                             $handle
     * @return Generator<int, array<string, mixed>>
     */
    protected function readRows(string $format, $handle): Generator
    {
        return match ($format) {
            'csv'   => $this->readCsv($handle),
            default => $this->readJson($handle),
        };
    }

    /**
     * @param  resource                             $handle
     * @return Generator<int, array<string, mixed>>
     */
    protected function readJson($handle): Generator
    {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);

            if (! is_array($decoded)) {
                throw new RuntimeException('Invalid JSON payload encountered during import.');
            }

            /** @var array<string, mixed> $decoded */
            yield $this->normalizeRow($decoded);
        }
    }

    /**
     * @param  resource                             $handle
     * @return Generator<int, array<string, mixed>>
     */
    protected function readCsv($handle): Generator
    {
        $headers = null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(
                    static fn ($header): string => (string) ($header ?? ''),
                    $row,
                );

                continue;
            }

            if (count($row) === 1 && $row[0] === null) {
                continue;
            }

            $data = [];

            foreach ($headers as $index => $header) {
                $value = $row[$index] ?? null;

                if ($value === self::NULL_PLACEHOLDER || $value === null) {
                    $value = null;
                }

                $data[$header] = $value;
            }

            yield $this->normalizeRow($data);
        }
    }

    /**
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row): array
    {
        if (! array_key_exists('id', $row)) {
            throw new RuntimeException('Cannot import row without an id column.');
        }

        return $row;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    protected function persistBatch(string $table, array $rows): void
    {
        DB::table($table)->upsert($rows, ['id']);
    }

    /**
     * @return array<int, string>
     */
    protected function tableColumns(string $table): array
    {
        /** @var array<int, string> $columns */
        $columns = Schema::getColumnListing($table);

        return $columns;
    }

    /**
     * @return resource
     */
    protected function openHandle(string $path, string $mode): mixed
    {
        $handle = fopen($path, $mode);

        if (! is_resource($handle)) {
            throw new RuntimeException("Unable to open [{$path}] using mode [{$mode}].");
        }

        return $handle;
    }
}
