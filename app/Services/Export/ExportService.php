<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\Contracts\ExportWriter;
use App\Services\Export\Writers\CsvExportWriter;
use App\Services\Export\Writers\PdfExportWriter;
use App\Services\Export\Writers\XlsxExportWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

final class ExportService
{
    private string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? config('filesystems.exports_disk', config('filesystems.default', 'public'));
    }

    public function queue(ExportRequestData $data): Export
    {
        $payload = $data->toPayload();
        $exportable = $this->resolveExportable($payload['exportable']);
        $columns = $this->resolveColumns($exportable, $payload['columns']);

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

        Bus::dispatch(new ProcessExportJob($export->getKey()));

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
            $columns = $this->resolveColumns($exportable, $export->columns);
            $query = $this->buildQuery($exportable, $export->exportable_options ?? []);
            $writer = $this->makeWriter($export->format);
            $headers = Collection::make($columns)->map(fn (ExportColumn $column) => $column->label)->values()->all();
            $path = $this->artifactPath($export);

            $writer->open($export->artifact_disk ?? $this->disk, $path, $headers);

            $total = 0;
            $query->chunkById(250, function (Collection $chunk) use (&$total, $writer, $exportable, $columns): void {
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
                $export->requestedBy->notify(new ExportReadyNotification($export, $this->downloadUrl($export)));
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

    public function downloadUrl(Export $export, ?int $minutes = null): string
    {
        $expires = now()->addMinutes($minutes ?? 60);

        return URL::temporarySignedRoute('exports.signed-download', $expires, [
            'export' => $export,
        ]);
    }

    /**
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
        $instance = app($class);

        if (! $instance instanceof Exportable) {
            throw new \InvalidArgumentException(sprintf('%s must implement %s', $class, Exportable::class));
        }

        return $instance;
    }

    private function makeWriter(string $format): ExportWriter
    {
        return match ($format) {
            'csv' => new CsvExportWriter,
            'xlsx' => new XlsxExportWriter,
            'pdf' => new PdfExportWriter,
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function buildQuery(Exportable $exportable, array $options): Builder
    {
        $query = $exportable->query($options);
        $ids = Arr::get($options, 'record_ids', []);

        if ($ids) {
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
}
