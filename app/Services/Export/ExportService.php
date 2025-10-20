<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Data\ExportRequestData;
use App\Enums\ExportStatus;
use App\Enums\ExportType;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class ExportService
{
    public function __construct(
        private readonly ExportWriterFactory $writerFactory,
    ) {}

    public function queueExport(ExportRequestData $request, User $user): Export
    {
        $columns = $this->resolveColumns($request->entity, $request->columns ?? []);

        $export = Export::query()->create([
            'requested_by' => $user->getKey(),
            'type' => $request->entity,
            'format' => $request->format,
            'status' => ExportStatus::PENDING,
            'filters' => $this->prepareFilters($request),
            'columns' => $columns,
            'locale' => $request->normalizedLocale(),
            'timezone' => $request->normalizedTimezone(),
        ]);

        ProcessExportJob::dispatch($export)->onQueue('exports');

        return $export;
    }

    public function process(Export $export): void
    {
        $config = $this->entityConfig($export->type);
        $writer = $this->writerFactory->make($export->format);
        $timestamp = now()->timezone($export->timezone)->format('Ymd_His');
        $dateFolder = now()->timezone($export->timezone)->format('Y-m-d');
        $fileName = sprintf('%s_%s.%s', $export->type->value, $timestamp, $writer->extension());
        $relativePath = sprintf('exports/%s/%s/%s', $export->type->value, $dateFolder, $fileName);

        $export->forceFill([
            'status' => ExportStatus::PROCESSING,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'mime_type' => $writer->mimeType(),
        ])->save();

        $originalLocale = app()->getLocale();
        $originalTz = date_default_timezone_get();

        app()->setLocale($export->locale);
        date_default_timezone_set($export->timezone);

        $columns = $export->columns ?? $this->resolveColumns($export->type, []);

        try {
            $writer->open($export, $columns, $relativePath);

            $query = $this->buildQuery($config, $export);

            $chunkSize = (int) config('exports.chunk_size', 1000);
            $totalRows = 0;

            $query->chunkById($chunkSize, function ($models) use ($export, $writer, $columns, &$totalRows, $config): void {
                $rows = [];

                foreach ($models as $model) {
                    $rows[] = $this->mapRow($export, $model, $columns, $config);
                }

                $writer->append($rows);
                $totalRows += count($rows);
            });

            $writer->close();

            $export->forceFill([
                'status' => ExportStatus::COMPLETED,
                'total_rows' => $totalRows,
                'completed_at' => now(),
                'expires_at' => now()->addMinutes((int) config('exports.ttl_minutes', 1440)),
            ])->save();

            if ($export->relationLoaded('requester')) {
                $export->requester->notify(new ExportReadyNotification($export));
            } else {
                $export->loadMissing('requester')->requester?->notify(new ExportReadyNotification($export));
            }
        } catch (Throwable $exception) {
            $writer->close();
            $export->forceFill([
                'status' => ExportStatus::FAILED,
            ])->save();

            throw $exception;
        } finally {
            app()->setLocale($originalLocale);
            date_default_timezone_set($originalTz);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function entityConfig(ExportType $entity): array
    {
        $config = config(sprintf('exports.entities.%s', $entity->value));

        if (! is_array($config)) {
            throw new InvalidArgumentException(sprintf('Export entity [%s] is not configured.', $entity->value));
        }

        return $config;
    }

    private function resolveColumns(ExportType $entity, array $requested): array
    {
        $config = $this->entityConfig($entity);
        $available = $config['columns'] ?? [];

        if ($available === []) {
            throw new InvalidArgumentException(sprintf('Export entity [%s] has no columns configured.', $entity->value));
        }

        $keys = $requested !== [] ? array_values(array_intersect($requested, array_keys($available))) : array_keys($available);

        if ($keys === []) {
            $keys = array_keys($available);
        }

        return array_map(static function (string $key) use ($available): array {
            $definition = $available[$key];

            return [
                'key' => $key,
                'label' => $definition['label'],
                'type' => $definition['type'],
            ];
        }, $keys);
    }

    private function prepareFilters(ExportRequestData $request): array
    {
        $filters = $request->filters ?? [];

        if (! empty($request->ids)) {
            $filters['ids'] = array_values($request->ids);
        }

        return $filters;
    }

    private function buildQuery(array $config, Export $export): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        /** @var Model $model */
        $model = new $modelClass();

        $query = $modelClass::query();

        foreach ($config['with'] ?? [] as $relation) {
            $query->with($relation);
        }

        foreach ($config['with_count'] ?? [] as $relation) {
            $query->withCount($relation);
        }

        $filters = $export->filters ?? [];

        if (! empty($filters['ids'])) {
            $query->whereIn($model->qualifyColumn('id'), $filters['ids']);
        }

        foreach ($filters as $key => $value) {
            if (in_array($key, ['ids'], true)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (Str::endsWith($key, '_from')) {
                $column = Str::beforeLast($key, '_from');
                $query->whereDate($model->qualifyColumn($column), '>=', $value);

                continue;
            }

            if (Str::endsWith($key, '_until')) {
                $column = Str::beforeLast($key, '_until');
                $query->whereDate($model->qualifyColumn($column), '<=', $value);

                continue;
            }

            if (is_array($value)) {
                $query->whereIn($model->qualifyColumn($key), $value);
            } else {
                $query->where($model->qualifyColumn($key), $value);
            }
        }

        return $query->orderBy($model->qualifyColumn($model->getKeyName()))
            ->select($model->getTable().'.*');
    }

    private function mapRow(Export $export, Model $model, array $columns, array $config): array
    {
        $row = [];

        foreach ($columns as $column) {
            $definition = $config['columns'][$column['key']] ?? null;

            if (! $definition) {
                continue;
            }

            $value = $definition['resolver']($model);

            if ($value instanceof \DateTimeInterface) {
                $value = $value->setTimezone(new \DateTimeZone($export->timezone))->format('Y-m-d H:i:s');
            } elseif ($definition['type'] === 'currency' && is_numeric($value)) {
                $value = number_format((float) $value, 2, '.', '');
            }

            $row[$column['label']] = $value ?? '';
        }

        return $row;
    }
}
