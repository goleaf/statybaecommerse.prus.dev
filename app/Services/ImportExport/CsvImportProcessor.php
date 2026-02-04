<?php

declare(strict_types=1);

namespace App\Services\ImportExport;

use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class CsvImportProcessor
{
    /**
     * @param array<array<string, mixed>> $rows
     * @param array<string, string>       $columnMap
     */
    /**
     * @param  array<array<string, mixed>>                                     $rows
     * @param  array<string, string>                                           $columnMap
     * @return array{processedRows: int, successfulRows: int, failedRows: int}
     */
    public function processChunk(Import $import, Importer $importer, array $rows, array $columnMap): array
    {
        $processedRows = 0;
        $successfulRows = 0;
        $failedRows = [];

        DB::transaction(function () use ($import, $importer, $rows, $columnMap, &$processedRows, &$successfulRows, &$failedRows): void {
            foreach ($rows as $row) {
                $row = $this->utf8Encode($row);

                try {
                    ($importer)($row);
                    $successfulRows++;
                } catch (RowImportFailedException $exception) {
                    $failedRows[] = $this->formatFailedRow($row, $exception->getMessage(), $importer, $columnMap);
                } catch (ValidationException $exception) {
                    $failedRows[] = $this->formatFailedRow($row, collect($exception->errors())->flatten()->implode(' '), $importer, $columnMap);
                } catch (Throwable $exception) {
                    report($exception);

                    $failedRows[] = $this->formatFailedRow($row, null, $importer, $columnMap);
                }

                $processedRows++;
            }

            $import::query()
                ->whereKey($import)
                ->lockForUpdate()
                ->update([
                    'processed_rows'  => new Expression('processed_rows + ' . $processedRows),
                    'successful_rows' => new Expression('successful_rows + ' . $successfulRows),
                ]);

            $import::query()
                ->whereKey($import)
                ->whereColumn('processed_rows', '>', 'total_rows')
                ->lockForUpdate()
                ->update([
                    'processed_rows' => new Expression('total_rows'),
                ]);

            $import::query()
                ->whereKey($import)
                ->whereColumn('successful_rows', '>', 'total_rows')
                ->lockForUpdate()
                ->update([
                    'successful_rows' => new Expression('total_rows'),
                ]);

            if (count($failedRows)) {
                $import->failedRows()->createMany($failedRows);
            }
        });

        return [
            'processedRows'  => $processedRows,
            'successfulRows' => $successfulRows,
            'failedRows'     => count($failedRows),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string> $columnMap
     * @return array<string, mixed>
     */
    private function formatFailedRow(array $data, ?string $validationError, Importer $importer, array $columnMap): array
    {
        return [
            'data'             => $this->filterSensitiveData($data, $importer, $columnMap),
            'validation_error' => $validationError,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string> $columnMap
     * @return array<string, mixed>
     */
    private function filterSensitiveData(array $data, Importer $importer, array $columnMap): array
    {
        return array_reduce(
            $importer->getColumns(),
            function (array $carry, ImportColumn $column) use ($columnMap): array {
                if (! $column->isSensitive()) {
                    return $carry;
                }

                $csvHeader = $columnMap[$column->getName()] ?? null;

                if (blank($csvHeader)) {
                    return $carry;
                }

                if (! array_key_exists($csvHeader, $carry)) {
                    return $carry;
                }

                unset($carry[$csvHeader]);

                return $carry;
            },
            initial: $data,
        );
    }

    private function utf8Encode(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->utf8Encode(...), $value);
        }

        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }
}
