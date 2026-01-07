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

    /**
     * Canonical JSON column map keyed by the destination table so we can re-encode
     * structured payloads consistently during import (prevents string comparison
     * drift across storage engines that reorder JSON keys).
     */
    private const JSON_COLUMN_MAP = [
        'attributes' => ['validation_rules', 'meta_data', 'options'],
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

        File::ensureDirectoryExists(dirname($path));
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

        foreach ($this->readRows($format, $handle, $table) as $row) {
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
    protected function readRows(string $format, $handle, string $table): Generator
    {
        return match ($format) {
            'csv'   => $this->readCsv($handle, $table),
            default => $this->readJson($handle, $table),
        };
    }

    /**
     * @param  resource                             $handle
     * @return Generator<int, array<string, mixed>>
     */
    protected function readJson($handle, string $table): Generator
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
            yield $this->normalizeRow($decoded, $table);
        }
    }

    /**
     * @param  resource                             $handle
     * @return Generator<int, array<string, mixed>>
     */
    protected function readCsv($handle, string $table): Generator
    {
        $headers = null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(
                    static fn ($header): string => $header ?? '',
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

            yield $this->normalizeRow($data, $table);
        }
    }

    /**
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row, ?string $table = null): array
    {
        if (! array_key_exists('id', $row)) {
            throw new RuntimeException('Cannot import row without an id column.');
        }

        if ($table !== null) {
            $row = $this->normalizeJsonColumns($table, $row);
        }

        return $row;
    }

    /**
     * Apply canonical JSON encoding to configured columns so JSON structures stay
     * byte-identical after round trips (essential for comparisons in tests).
     *
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeJsonColumns(string $table, array $row): array
    {
        foreach ($this->jsonColumnsFor($table) as $column) {
            if (! array_key_exists($column, $row)) {
                continue;
            }

            $row[$column] = $this->canonicalizeJsonValue($row[$column]);
        }

        return $row;
    }

    /**
     * Determine which columns should be treated as JSON structures during import.
     *
     * @return array<int, string>
     */
    private function jsonColumnsFor(string $table): array
    {
        return self::JSON_COLUMN_MAP[$table] ?? [];
    }

    /**
     * Convert JSON-encoded payloads (string/array) into canonical strings while
     * preserving numeric precision and original key ordering.
     */
    private function canonicalizeJsonValue(mixed $value): mixed
    {
        if ($value === null || $value === self::NULL_PLACEHOLDER) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return $value;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $value;
            }

            if ($decoded === null) {
                return null;
            }

            return $this->encodeCanonicalJson($decoded);
        }

        if (is_array($value)) {
            return $this->encodeCanonicalJson($value);
        }

        return $value;
    }

    /**
     * Encode JSON payloads using consistent flags to avoid escaping drift while
     * keeping the original key order intact.
     */
    private function encodeCanonicalJson(mixed $data): string
    {
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

        if ($encoded === false) {
            throw new RuntimeException('Unable to encode canonical JSON payload.');
        }

        return $encoded;
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
