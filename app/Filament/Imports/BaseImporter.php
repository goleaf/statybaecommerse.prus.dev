<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

abstract class BaseImporter extends Importer
{
    protected static function calculateFailedRowsCount(Import $import): int
    {
        $total = max(0, (int) ($import->total_rows ?? 0));
        $processed = max(0, min((int) ($import->processed_rows ?? 0), $total));
        $successful = max(0, min((int) ($import->successful_rows ?? 0), $processed));

        return max(0, $processed - $successful);
    }

    protected function beforeValidate(): void
    {
        $this->ensureUniqueColumnValues();
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    /**
     * @return array<string, array<string>>
     */
    public static function getColumnGroups(): array
    {
        return [
            'General' => [],
        ];
    }

    protected function ensureUniqueColumnValues(): void
    {
        $model = app(static::getModel());
        $schema = $model->getConnection()->getSchemaBuilder();

        $indexes = $schema->getIndexes($model->getTable());

        $uniqueColumns = collect($indexes)
            ->filter(fn (array $index): bool => ($index['unique'] ?? false) && ! ($index['primary'] ?? false))
            ->map(fn (array $index): array => $index['columns'] ?? [])
            ->filter(fn (array $columns): bool => count($columns) === 1)
            ->map(fn (array $columns): string => (string) $columns[0])
            ->unique()
            ->values();

        foreach ($uniqueColumns as $column) {
            if (! array_key_exists($column, $this->data)) {
                continue;
            }

            $value = $this->data[$column];

            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            $this->data[$column] = $this->makeUniqueColumnValue($model, $column, $value);
        }
    }

    protected function makeUniqueColumnValue(Model $model, string $column, mixed $value): mixed
    {
        $normalized = $this->normalizeUniqueValue($column, $value);

        if ($this->isUniqueValueAvailable($model, $column, $normalized)) {
            return $normalized;
        }

        if ($this->looksLikeEmailColumn($column, $normalized)) {
            return $this->makeUniqueEmailValue($model, $column, (string) $normalized);
        }

        if ($this->isNumericColumn($model, $column) && is_numeric($normalized)) {
            return $this->makeUniqueNumericValue($model, $column, $normalized);
        }

        return $this->makeUniqueStringValue($model, $column, (string) $normalized);
    }

    protected function normalizeUniqueValue(string $column, mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($this->isSlugColumn($column)) {
            $value = Str::slug($value);
        }

        return $value;
    }

    protected function isSlugColumn(string $column): bool
    {
        return Str::contains(Str::lower($column), 'slug');
    }

    protected function looksLikeEmailColumn(string $column, mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return Str::contains(Str::lower($column), 'email') && str_contains($value, '@');
    }

    protected function isUniqueValueAvailable(Model $model, string $column, mixed $value): bool
    {
        $query = $model->newQuery()->withoutGlobalScopes()->where($column, $value);

        if ($this->record?->exists) {
            $query->whereKeyNot($this->record->getKey());
        }

        return ! $query->exists();
    }

    protected function isNumericColumn(Model $model, string $column): bool
    {
        try {
            $type = $model->getConnection()->getSchemaBuilder()->getColumnType($model->getTable(), $column);
        } catch (Throwable) {
            return false;
        }

        $type = Str::lower($type);

        return Str::contains($type, ['int', 'decimal', 'float', 'double']);
    }

    protected function makeUniqueNumericValue(Model $model, string $column, mixed $value): int|float
    {
        $base = is_numeric($value) ? (float) $value : 0.0;
        $counter = 1;

        while (true) {
            $candidate = $base + $counter;

            if ($this->isUniqueValueAvailable($model, $column, $candidate)) {
                return Str::contains(
                    Str::lower($model->getConnection()->getSchemaBuilder()->getColumnType($model->getTable(), $column)),
                    'int'
                ) ? (int) $candidate : $candidate;
            }

            $counter++;
        }
    }

    protected function makeUniqueStringValue(Model $model, string $column, string $value): string
    {
        $base = $value !== '' ? $value : Str::random(8);
        $counter = 1;

        while (true) {
            $candidate = "{$base}-{$counter}";

            if ($this->isUniqueValueAvailable($model, $column, $candidate)) {
                return $candidate;
            }

            $counter++;
        }
    }

    protected function makeUniqueEmailValue(Model $model, string $column, string $value): string
    {
        [$local, $domain] = array_pad(explode('@', $value, 2), 2, '');

        if ($domain === '') {
            return $this->makeUniqueStringValue($model, $column, $value);
        }

        $local = preg_replace('/-\d+$/', '', $local) ?: $local;
        $counter = 1;

        while (true) {
            $candidate = "{$local}-{$counter}@{$domain}";

            if ($this->isUniqueValueAvailable($model, $column, $candidate)) {
                return $candidate;
            }

            $counter++;
        }
    }
}
