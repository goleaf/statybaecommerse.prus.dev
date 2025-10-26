<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Enums\ExportType;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\Contracts\ExportWriter;
use App\Services\Export\Exporters\OrderExport;
use App\Services\Export\Exporters\ProductExport;
use App\Services\Export\Exporters\UserExport;
use App\Services\Export\Writers\CsvExportWriter;
use App\Services\Export\Writers\PdfExportWriter;
use App\Services\Export\Writers\XlsxExportWriter;
use App\Support\Exports\ExportUrlGenerator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
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

        $configuredDisk = $config['disk'] ?? config('filesystems.default', 'local');
        $this->disk = $disk ?? (is_string($configuredDisk) ? $configuredDisk : 'local');
        $this->chunkSize = $this->resolveInteger($config['chunk_size'] ?? null, 250);
        $this->downloadUrlTtl = $this->resolveInteger($config['download_url_ttl'] ?? null, 60);

        $this->writerMap = [
            'csv'  => CsvExportWriter::class,
            'xlsx' => XlsxExportWriter::class,
            'pdf'  => PdfExportWriter::class,
        ];

        foreach (($config['formats'] ?? []) as $key => $writer) {
            if (! is_string($key) || ! is_string($writer) || ! is_subclass_of($writer, ExportWriter::class)) {
                continue;
            }

            $this->writerMap[Str::lower($key)] = $writer;
        }
    }

    public function queue(ExportRequestData $data): Export
    {
        $exportable = $this->resolveExportableFromRequest($data);

        return $this->queueExportable($exportable, $data);
    }

    public function queueExport(ExportRequestData $data, User $user): Export
    {
        $data->userId ??= $user->getKey();
        $exportable = $this->resolveExportableFromRequest($data);

        return $this->queueExportable($exportable, $data, $user);
    }

    public function process(Export|int $export): Export
    {
        $model = $this->resolveExportModel($export);

        if ($model->status === ExportStatus::Completed) {
            return $model;
        }

        $model->forceFill([
            'status'         => ExportStatus::Processing,
            'processed_rows' => 0,
            'failure_reason' => null,
            'failed_at'      => null,
        ])->save();

        $disk = $model->artifact_disk ?? $this->disk;
        $path = null;

        try {
            $exportable = $this->resolveExportable($model->exportable_type);
            $columns = $this->resolveColumns($exportable, $model->columns ?? []);
            $query = $this->buildQuery($exportable, $model->exportable_options ?? []);
            $writer = $this->makeWriter($model->format);
            $headers = Collection::make($columns)
                ->map(static fn (ExportColumn $column): string => $column->label)
                ->values()
                ->all();

            $path = $this->artifactPath($model);

            $writer->open($disk, $path, $headers);

            $total = 0;
            $query->chunkById($this->chunkSize, function (Collection $chunk) use (&$total, $writer, $exportable, $columns): void {
                /** @var Model $record */
                foreach ($chunk as $record) {
                    $writer->append($exportable->map($record, $columns));
                    $total++;
                }
            });

            $writer->close();

            $model->forceFill([
                'status'            => ExportStatus::Completed,
                'artifact_path'     => $path,
                'artifact_filename' => $this->buildFileName($exportable, $model),
                'completed_at'      => now(),
                'total_rows'        => $total,
                'processed_rows'    => $total,
            ])->save();

            $this->notifySuccess($model);
        } catch (Throwable $exception) {
            Log::error('Export failed', [
                'export_id' => $model->getKey(),
                'exception' => $exception,
            ]);

            $model->forceFill([
                'status'         => ExportStatus::Failed,
                'failed_at'      => now(),
                'failure_reason' => $exception->getMessage(),
            ])->save();

            $this->notifyFailure($model);

            if ($path !== null) {
                Storage::disk($disk)->delete($path);
            }
        }

        return $model;
    }

    private function queueExportable(Exportable $exportable, ExportRequestData $data, ?User $user = null): Export
    {
        $columns = $this->resolveColumns($exportable, $data->requestedColumns());
        $options = [
            'record_ids' => $data->recordIdentifiers(),
            'filters'    => $data->filters,
            'meta'       => $data->metadata(),
        ];

        $export = Export::query()->create([
            'name'               => $data->name ?: $exportable->name(),
            'format'             => $data->normalizedFormat(),
            'status'             => ExportStatus::Queued,
            'exportable_type'    => $exportable::class,
            'columns'            => array_keys($columns),
            'exportable_options' => $options,
            'artifact_disk'      => $this->disk,
            'requested_by'       => $user?->getKey() ?? $data->userId,
        ]);

        ProcessExportJob::dispatch($export->getKey());

        return $export;
    }

    /**
     * @param  array<int, string>          $requested
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
                    throw new InvalidArgumentException('At least one column must be selected for export.');
                }
            })
            ->all();
    }

    private function resolveExportable(string $class): Exportable
    {
        $instance = $this->container->make($class);

        if (! $instance instanceof Exportable) {
            throw new InvalidArgumentException(sprintf('%s must implement %s', $class, Exportable::class));
        }

        return $instance;
    }

    private function makeWriter(string $format): ExportWriter
    {
        $format = Str::lower($format);

        if (! array_key_exists($format, $this->writerMap)) {
            throw new InvalidArgumentException("Unsupported export format: {$format}");
        }

        $writer = $this->container->make($this->writerMap[$format]);

        if (! $writer instanceof ExportWriter) {
            throw new InvalidArgumentException(sprintf('Writer for format %s must implement %s', $format, ExportWriter::class));
        }

        return $writer;
    }

    /**
     * @param array<string, mixed> $options
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

        return 'exports/' . $export->uuid . '.' . $extension;
    }

    private function buildFileName(Exportable $exportable, Export $export): string
    {
        $base = Str::slug($exportable->fileName($export));
        $timestamp = now()->format('Ymd_His');
        $extension = Str::lower($export->format);

        return trim($base . '-' . $timestamp . '.' . $extension, '-');
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

    private function resolveExportModel(Export|int $export): Export
    {
        if ($export instanceof Export) {
            return $export->fresh() ?? $export;
        }

        return Export::query()->findOrFail($export);
    }

    private function resolveExportableFromRequest(ExportRequestData $data): Exportable
    {
        if (is_string($data->exportable) && $data->exportable !== '') {
            return $this->resolveExportable($data->exportable);
        }

        $entity = $data->entityEnum();

        if ($entity === null) {
            throw new InvalidArgumentException('Exportable class or entity must be provided.');
        }

        return match ($entity) {
            ExportType::ORDERS   => $this->resolveExportable(OrderExport::class),
            ExportType::PRODUCTS => $this->resolveExportable(ProductExport::class),
            ExportType::USERS    => $this->resolveExportable(UserExport::class),
        };
    }

    private function notifySuccess(Export $export): void
    {
        $export->loadMissing('requestedBy');
        $user = $export->requestedBy;

        if (! $user instanceof User) {
            return;
        }

        $url = ExportUrlGenerator::temporarySignedDownloadUrl($export, $this->downloadUrlTtl);

        $user->notify(new ExportCompletedNotification($export, $url));
        $user->notify(new ExportReadyNotification($export, $url, $this->downloadUrlTtl));
    }

    private function notifyFailure(Export $export): void
    {
        $export->loadMissing('requestedBy');
        $user = $export->requestedBy;

        if (! $user instanceof User) {
            return;
        }

        $user->notify(new ExportFailedNotification($export));
    }
}
