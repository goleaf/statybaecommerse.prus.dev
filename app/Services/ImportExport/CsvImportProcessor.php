<?php

declare(strict_types=1);

namespace App\Services\ImportExport;

use App\Models\ImportRowResult;
use App\Support\ImportExport\ProgressCounter;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $rowResults = [];
        $timestamp = now();

        DB::transaction(function () use ($import, $importer, $rows, $columnMap, $timestamp, &$processedRows, &$successfulRows, &$failedRows, &$rowResults): void {
            foreach ($rows as $row) {
                $rowNumber = $row['__row_number'] ?? null;
                unset($row['__row_number']);

                $row = $this->utf8Encode($row);

                try {
                    ($importer)($row);
                    $successfulRows++;
                    $rowResults[] = $this->formatRowResult(
                        $importer,
                        $row,
                        $columnMap,
                        $rowNumber,
                        $timestamp,
                    );
                } catch (RowImportFailedException $exception) {
                    $failedRows[] = $this->formatFailedRow($row, $exception->getMessage(), $importer, $columnMap);
                    $rowResults[] = $this->formatRowFailure(
                        $importer,
                        $row,
                        $columnMap,
                        $rowNumber,
                        $exception->getMessage(),
                        $timestamp,
                    );
                } catch (ValidationException $exception) {
                    $message = collect($exception->errors())->flatten()->implode(' ');
                    $failedRows[] = $this->formatFailedRow($row, $message, $importer, $columnMap);
                    $rowResults[] = $this->formatRowFailure(
                        $importer,
                        $row,
                        $columnMap,
                        $rowNumber,
                        $message,
                        $timestamp,
                    );
                } catch (Throwable $exception) {
                    report($exception);

                    $failedRows[] = $this->formatFailedRow($row, null, $importer, $columnMap);
                    $rowResults[] = $this->formatRowFailure(
                        $importer,
                        $row,
                        $columnMap,
                        $rowNumber,
                        $exception->getMessage(),
                        $timestamp,
                    );
                }

                $processedRows++;
            }

            /** @var Import|null $lockedImport */
            $lockedImport = $import::query()
                ->whereKey($import)
                ->lockForUpdate()
                ->first();

            if ($lockedImport) {
                $totalRows = ProgressCounter::normalizeTotal((int) ($lockedImport->total_rows ?? 0));
                $nextProcessedRows = ProgressCounter::normalizeProcessed(
                    (int) ($lockedImport->processed_rows ?? 0) + $processedRows,
                    $totalRows,
                );
                $nextSuccessfulRows = ProgressCounter::normalizeSuccessful(
                    (int) ($lockedImport->successful_rows ?? 0) + $successfulRows,
                    $nextProcessedRows,
                    $totalRows,
                );

                $lockedImport->forceFill([
                    'processed_rows'  => $nextProcessedRows,
                    'successful_rows' => $nextSuccessfulRows,
                ])->save();
            }

            if (count($failedRows)) {
                $import->failedRows()->createMany($failedRows);
            }

            if (count($rowResults)) {
                ImportRowResult::query()->insert($rowResults);
            }
        });

        return [
            'processedRows'  => $processedRows,
            'successfulRows' => $successfulRows,
            'failedRows'     => count($failedRows),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function formatRowResult(Importer $importer, array $data, array $columnMap, ?int $rowNumber, mixed $timestamp): array
    {
        $record = $importer->getRecord();
        $changedFields = $record ? array_keys($record->getChanges()) : [];

        $action = 'skipped';
        if ($record?->wasRecentlyCreated) {
            $action = 'created';
        } elseif ($record && count($changedFields)) {
            $action = 'updated';
        }

        $message = match ($action) {
            'created' => 'Created.',
            'updated' => 'Updated fields: ' . ($changedFields ? implode(', ', $changedFields) : 'none'),
            default   => 'No changes.',
        };

        return [
            'import_id'      => $importer->getImport()->getKey(),
            'row_number'     => $rowNumber,
            'status'         => 'success',
            'action'         => $action,
            'message'        => $message,
            'error_message'  => null,
            'changed_fields' => json_encode($changedFields),
            'data'           => json_encode($this->filterSensitiveData($data, $importer, $columnMap)),
            'created_at'     => $timestamp,
            'updated_at'     => $timestamp,
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function formatRowFailure(Importer $importer, array $data, array $columnMap, ?int $rowNumber, ?string $errorMessage, mixed $timestamp): array
    {
        $message = filled($errorMessage) ? Str::limit($errorMessage, 240) : 'Failed to import.';

        return [
            'import_id'      => $importer->getImport()->getKey(),
            'row_number'     => $rowNumber,
            'status'         => 'failed',
            'action'         => 'error',
            'message'        => $message,
            'error_message'  => $errorMessage,
            'changed_fields' => json_encode([]),
            'data'           => json_encode($this->filterSensitiveData($data, $importer, $columnMap)),
            'created_at'     => $timestamp,
            'updated_at'     => $timestamp,
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
