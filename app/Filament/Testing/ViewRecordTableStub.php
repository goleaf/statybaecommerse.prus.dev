<?php

declare(strict_types=1);

namespace App\Filament\Testing;

use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stringable;

/**
 * ViewRecordTableStub augments Filament's base ViewRecord page with minimal table
 * functionality so table-centric Livewire assertions operate during testing.
 */
final class ViewRecordTableStub extends ViewRecord implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    /**
     * Provide a narrow table definition that only surfaces the current record.
     */
    public function table(Table $table): Table
    {
        /** @var class-string<\Filament\Resources\Resource> $resourceClass */
        $resourceClass = self::getResource();

        return $table
            ->query(function () use ($resourceClass): Builder {
                // Clone the resource query each time so we avoid mutating a shared builder during assertions.
                /** @var Builder<Model> $query */
                $query = clone $resourceClass::getEloquentQuery();

                return $query->whereKey($this->getRecordKey());
            })
            ->columns([
                TextColumn::make($this->getRecordTitleColumn()),
            ])
            ->paginated(false);
    }

    /**
     * Resolve the active record's primary key for the table query.
     */
    private function getRecordKey(): int|string
    {
        $record = $this->getRecord();

        return $this->normaliseRecordKey($record);
    }

    /**
     * Normalise the provided record value into a string or integer key.
     *
     * @param Model|array<string, mixed>|Stringable|string|int|null $record
     */
    private function normaliseRecordKey(Model|array|Stringable|string|int|null $record): int|string
    {
        if ($record instanceof Model) {
            $key = $record->getKey();

            if (is_int($key) || is_string($key)) {
                return $key;
            }

            if (is_scalar($key)) {
                return (string) $key;
            }

            return '';
        }

        if (is_array($record)) {
            $candidate = $record['id'] ?? reset($record);

            if (is_int($candidate) || is_string($candidate)) {
                return $candidate;
            }

            if (is_scalar($candidate)) {
                return (string) $candidate;
            }

            return '';
        }

        if ($record instanceof Stringable) {
            return (string) $record;
        }

        if (is_string($record) || is_int($record)) {
            return $record;
        }

        if (is_object($record) && method_exists($record, '__toString')) {
            return (string) $record->__toString();
        }

        return '';
    }

    /**
     * Determine the column name to display in the test-only table.
     */
    private function getRecordTitleColumn(): string
    {
        /** @var class-string<\Filament\Resources\Resource> $resourceClass */
        $resourceClass = self::getResource();

        $column = $resourceClass::getRecordTitleAttribute();

        return is_string($column) && $column !== '' ? $column : 'id';
    }
}
