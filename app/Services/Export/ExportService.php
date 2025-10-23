<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Jobs\ProcessExport;
use App\Models\Export;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use App\Services\Export\Contracts\DefinesExportColumns;
use App\Services\Export\Writers\ExportWriterFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class ExportService
{
    public function __construct(
        private readonly ExportWriterFactory $writerFactory,
    ) {}

    /**
     * @param  class-string<\Filament\Resources\Resource&DefinesExportColumns>  $resourceClass
     * @param  iterable<int, Model|int|string>  $records
     * @param  array<int, string>  $columnKeys
     * @param  array<string, mixed>|null  $filters
     */
    public function queueResourceExport(
        string $resourceClass,
        iterable $records,
        array $columnKeys,
        ExportFormat $format,
        ?User $requestedBy = null,
        ?string $name = null,
        ?array $filters = null,
    ): Export {
        /** @phpstan-ignore-next-line */
        if (! is_subclass_of($resourceClass, DefinesExportColumns::class)) {
            throw new InvalidArgumentException(sprintf('%s must implement %s to support exports.', $resourceClass, DefinesExportColumns::class));
        }

        /** @phpstan-ignore-next-line */
        if (! is_subclass_of($resourceClass, \Filament\Resources\Resource::class)) {
            throw new InvalidArgumentException(sprintf('%s must extend %s to support exports.', $resourceClass, \Filament\Resources\Resource::class));
        }

        /** @var class-string<\Filament\Resources\Resource&DefinesExportColumns> $resourceClass */
        $resourceClass = $resourceClass;

        $modelClass = $resourceClass::getModel();
        /** @phpstan-ignore-next-line */
        if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException(sprintf('Resource %s does not expose a valid model.', $resourceClass));
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $modelClass;

        $recordIds = $this->normalizeRecordIdentifiers($records, $modelClass);
        $exportName = $name ?? sprintf('%s Export %s', Str::headline(class_basename($modelClass)), now()->format('Y-m-d H:i'));

        $chunkConfig = config('export.chunk_size');
        $chunkSize = 500;
        if (is_int($chunkConfig)) {
            $chunkSize = $chunkConfig;
        } elseif (is_string($chunkConfig) && is_numeric($chunkConfig)) {
            $chunkSize = (int) $chunkConfig;
        }

        $export = Export::query()->create([
            'user_id' => $requestedBy?->getKey(),
            'name' => $exportName,
            'resource' => $resourceClass,
            'model' => $modelClass,
            'format' => $format,
            'columns' => array_values($columnKeys),
            'selection' => $recordIds,
            'filters' => $filters,
            'status' => ExportStatus::Pending,
            'chunk_size' => $chunkSize,
        ]);

        ProcessExport::dispatch($export->id);

        return $export;
    }

    public function process(Export|string $export): void
    {
        $export = $export instanceof Export ? $export : Export::query()->findOrFail($export);

        try {
            $columns = $this->resolveColumns($export->resource, $export->columns);
            $headers = array_values(array_map(static fn (ExportColumn $column) => $column->label, $columns));

            /** @var class-string<Model> $modelClass */
            $modelClass = $export->model;
            $model = new $modelClass;
            $keyName = $model->getKeyName();

            $query = $modelClass::query();
            $selection = collect($export->selection ?? []);
            if ($selection->isNotEmpty()) {
                $query->whereIn($keyName, $selection->all());
            }

            $totalRows = $selection->isNotEmpty() ? $selection->count() : $query->count();
            $export->markProcessing($totalRows);

            $writer = $this->writerFactory->make($export->format);
            $writer->open($export, $headers);

            if ($selection->isNotEmpty()) {
                foreach ($selection->chunk($export->chunk_size) as $chunkedIds) {
                    $records = $modelClass::query()
                        ->whereIn($keyName, $chunkedIds->all())
                        ->orderBy($keyName)
                        ->get();

                    $rows = $this->mapRecordsToRows($records, $columns);
                    $writer->appendRows($rows);
                    $export->incrementProcessedRows($records->count());
                }
            } else {
                $modelClass::query()
                    ->orderBy($keyName)
                    ->chunkById($export->chunk_size, function ($records) use ($writer, $columns, $export): void {
                        $rows = $this->mapRecordsToRows($records, $columns);
                        $writer->appendRows($rows);
                        $export->incrementProcessedRows($records->count());
                    }, $keyName);
            }

            $path = $writer->close();
            $export->markCompleted($path);

            $export->load('user');
            if ($export->user) {
                $export->user->notify(new ExportCompletedNotification($export->id));
            }
        } catch (Throwable $exception) {
            $export->markFailed();
            Log::error('Export processing failed', [
                'export_id' => $export->id,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function makeSignedDownloadUrl(Export $export, ?\DateTimeInterface $expiresAt = null): string
    {
        $ttlConfig = config('export.download_url_ttl');
        $ttl = 60;
        if (is_int($ttlConfig)) {
            $ttl = $ttlConfig;
        } elseif (is_string($ttlConfig) && is_numeric($ttlConfig)) {
            $ttl = (int) $ttlConfig;
        }

        $expiration = $expiresAt ?? now()->addMinutes($ttl);

        return URL::temporarySignedRoute('api.exports.download', $expiration, [
            'export' => $export,
        ]);
    }

    /**
     * @param  array<int, string>  $columnKeys
     * @return array<string, ExportColumn>
     */
    private function resolveColumns(string $resourceClass, array $columnKeys): array
    {
        /** @phpstan-ignore-next-line */
        if (! is_subclass_of($resourceClass, DefinesExportColumns::class)) {
            throw new InvalidArgumentException(sprintf('%s must implement %s to support exports.', $resourceClass, DefinesExportColumns::class));
        }

        /** @var array<string, ExportColumn> $available */
        $available = $resourceClass::availableExportColumns();
        $columns = [];
        foreach ($columnKeys as $key) {
            if (isset($available[$key])) {
                $columns[$key] = $available[$key];
            }
        }

        if ($columns === []) {
            throw new InvalidArgumentException('At least one export column must be selected.');
        }

        return $columns;
    }

    /**
     * @param  iterable<int, Model|int|string>  $records
     * @return array<int, string>
     */
    private function normalizeRecordIdentifiers(iterable $records, string $modelClass): array
    {
        $collection = Collection::make($records);
        if ($collection->isEmpty()) {
            return [];
        }

        if ($collection->first() instanceof Model) {
            /** @var Collection<int, Model> $collection */
            return $collection->map(static function (Model $model): string {
                $key = $model->getKey();

                if (! is_scalar($key)) {
                    throw new InvalidArgumentException('Export selections must contain scalar identifiers.');
                }

                /** @var int|string $key */
                return (string) $key;
            })->all();
        }

        return $collection->map(static function ($id): string {
            if (! is_scalar($id)) {
                throw new InvalidArgumentException('Export selections must contain scalar identifiers.');
            }

            /** @var int|string $id */
            return (string) $id;
        })->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Model>|array<int, Model>  $records
     * @param  array<string, ExportColumn>  $columns
     * @return array<int, array<int, mixed>>
     */
    private function mapRecordsToRows($records, array $columns): array
    {
        $rows = [];
        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = $column->resolve($record);
            }
            $rows[] = $row;
        }

        return $rows;
    }
}
