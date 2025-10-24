<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExport;
use App\Models\Export;
use App\Notifications\ExportCompletedNotification;
use App\Notifications\ExportFailedNotification;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\Contracts\ExportWriter;
use App\Services\Export\Writers\CsvExportWriter;
use App\Services\Export\Writers\PdfExportWriter;
use App\Services\Export\Writers\XlsxExportWriter;
use App\Support\Exports\ExportUrlGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class ExportService
{
    private string $disk;

    private int $chunkSize;

    private int $downloadUrlTtl;

    /**
     * @var array<string, class-string<ExportWriter>>
     */
    private array $writerMap = [];

    public function __construct(private readonly Container $container, ?string $disk = null)
    {
        $config = config('export');

        if (! is_array($config)) {
            $config = [];
        }

        $configuredDisk = $config['disk'] ?? config('filesystems.default', 'public');
        $this->disk = $disk ?? (is_string($configuredDisk) ? $configuredDisk : 'public');
        $this->chunkSize = $this->resolveInteger($config['chunk_size'] ?? null, 250);
        $this->downloadUrlTtl = $this->resolveInteger($config['download_url_ttl'] ?? null, 60);
        $formats = $config['formats'] ?? [];

        if (is_array($formats)) {
            foreach ($formats as $key => $writer) {
                if (! is_string($key) || ! is_string($writer) || ! is_subclass_of($writer, ExportWriter::class)) {
                    continue;
                }

                $this->writerMap[Str::lower($key)] = $writer;
            }
        }
    }

    public function queue(ExportRequestData $data): Export
    {
        $payload = $data->toPayload();
        $exportable = $this->resolveExportable($payload['exportable']);

        /** @var array<int, string> $requestedColumns */
        $requestedColumns = $payload['columns'];
        $columns = $this->resolveColumns($exportable, $requestedColumns);

        $export = Export::query()->create([
            'name' => $payload['name'] ?: $exportable->name(),
            'format' => $payload['format'],
            'status' => ExportStatus::Queued,
            'exportable_type' => $payload['exportable'],
            'columns' => array_keys($columns),
            'exportable_options' => [
                'record_ids' => $payload['record_ids'],
                'filters' => $payload['filters'],
                'meta' => $payload['meta'],
            ],
            'artifact_disk' => $this->disk,
            'requested_by' => $payload['user_id'],
        ]);

        $exportId = $export->getKey();

        if (! is_int($exportId)) {
            throw new \UnexpectedValueException('Export primary key must be an integer.');
        }

        Bus::dispatch(new ProcessExport($exportId));

        return $export;
    }

    public function process(int $exportId): void
    {
        $export = Export::query()->findOrFail($exportId);

        if ($export->status === ExportStatus::Completed) {
            return;
        }

        $export->forceFill([
            'status' => ExportStatus::Processing,
            'processed_rows' => 0,
            'failure_reason' => null,
            'failed_at' => null,
        ])->save();

        try {
            $exportable = $this->resolveExportable($export->exportable_type);
            /** @var array<int, string> $columnKeys */
            $columnKeys = $export->columns ?? [];
            $columns = $this->resolveColumns($exportable, $columnKeys);
            $query = $this->buildQuery($exportable, $export->exportable_options ?? []);
            $writer = $this->makeWriter($export->format);
            $headers = Collection::make($columns)->map(fn (ExportColumn $column) => $column->label)->values()->all();
            $path = $this->artifactPath($export);

            $writer->open($export->artifact_disk ?? $this->disk, $path, $headers);

            $total = 0;
            $query->chunkById($this->chunkSize, function (Collection $chunk) use (&$total, $writer, $exportable, $columns): void {
                /** @var Model $model */
                foreach ($chunk as $model) {
                    $writer->append($exportable->map($model, $columns));
                    $total++;
                }
            });

            $writer->close();

            $export->forceFill([
                'status' => ExportStatus::Completed,
                'artifact_path' => $path,
                'artifact_filename' => $this->buildFileName($exportable, $export),
                'completed_at' => now(),
                'total_rows' => $total,
                'processed_rows' => $total,
            ])->save();

            if ($export->requestedBy) {
                $export->requestedBy->notify(new ExportReadyNotification(
                    $export,
                    ExportUrlGenerator::temporarySignedDownloadUrl($export),
                ));
            }
        } catch (Throwable $exception) {
            Log::error('Export failed', [
                'export_id' => $export->getKey(),
                'exception' => $exception,
            ]);

            $export->forceFill([
                'status' => ExportStatus::Failed,
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ])->save();

            if ($export->requestedBy) {
                $export->requestedBy->notify(new ExportFailedNotification($export));
            }

            if ($path ?? null) {
                Storage::disk($export->artifact_disk ?? $this->disk)->delete($path);
            }
        }
    }

    /**
     * @param  array<int, string>  $requested
     * @return array<string, ExportColumn>
     */
    private function resolveColumns(Exportable $exportable, array $requested): array
    {
        $available = $exportable->columns();
        $requested = array_values(array_unique($requested));

        if ($requested === []) {
            $requested = $exportable->defaultColumns();
        }

        return collect($requested)
            ->filter(fn (string $key): bool => array_key_exists($key, $available))
            ->mapWithKeys(fn (string $key): array => [$key => $available[$key]])
            ->tap(function (Collection $collection): void {
                if ($collection->isEmpty()) {
                    throw new \InvalidArgumentException('At least one column must be selected for export.');
                }
            })
            ->all();
    }

    private function resolveExportable(string $class): Exportable
    {
        $instance = $this->container->make($class);

        if (! $instance instanceof Exportable) {
            throw new \InvalidArgumentException(sprintf('%s must implement %s', $class, Exportable::class));
        }

        return $instance;
    }

    private function makeWriter(string $format): ExportWriter
    {
        $format = Str::lower($format);

        if (! array_key_exists($format, $this->writerMap)) {
            throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        $writer = $this->container->make($this->writerMap[$format]);

        if (! $writer instanceof ExportWriter) {
            throw new \InvalidArgumentException(sprintf('Writer for format %s must implement %s', $format, ExportWriter::class));
        }

        return $writer;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function buildQuery(Exportable $exportable, array $options): Builder
    {
        $query = $exportable->query($options);
        $ids = Arr::get($options, 'record_ids', []);

        if (is_array($ids) && $ids !== []) {
            $query->whereKey($ids);
        }

        return $query->orderBy($query->getModel()->getQualifiedKeyName());
    }

    private function artifactPath(Export $export): string
    {
        $extension = Str::lower($export->format);

        return 'exports/'.$export->uuid.'.'.$extension;
    }

    private function buildFileName(Exportable $exportable, Export $export): string
    {
        $base = Str::slug($exportable->fileName($export));
        $timestamp = now()->format('Ymd_His');
        $extension = Str::lower($export->format);

        return trim($base.'-'.$timestamp.'.'.$extension, '-');
    }

    private function resolveInteger(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }
}
