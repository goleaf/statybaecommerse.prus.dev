<?php

declare(strict_types=1);

namespace App\Services\Export\Exporters;

use App\Models\Export;
use App\Models\ProductHistory;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\ExportColumn;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ProductHistory\ProductHistoryListConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Exportable implementation for product history records, allowing queue jobs to
 * stream sanitized CSV/XLSX payloads that respect the same allow-listed query
 * parameters as the interactive API endpoints.
 */
final class ProductHistoryExport implements Exportable
{
    /**
     * Provide a stable export name for downstream notifications.
     */
    public function name(): string
    {
        return __('Product History Export');
    }

    /**
     * Describe the available columns along with their human friendly labels.
     *
     * @return array<string, ExportColumn>
     */
    public function columns(): array
    {
        return [
            'occurred_at' => new ExportColumn('occurred_at', __('Occurred At'), 'created_at'),
            'action' => new ExportColumn('action', __('Action'), 'action'),
            'field_name' => new ExportColumn('field_name', __('Field'), 'field_name'),
            'old_value' => new ExportColumn('old_value', __('Old Value'), 'old_value'),
            'new_value' => new ExportColumn('new_value', __('New Value'), 'new_value'),
            'description' => new ExportColumn('description', __('Description'), 'description'),
            'user_name' => new ExportColumn(
                'user_name',
                __('User'),
                resolver: static fn (ProductHistory $history): string => (string) ($history->user?->name ?? ''),
            ),
            'ip_address' => new ExportColumn('ip_address', __('IP Address'), 'ip_address'),
        ];
    }

    /**
     * Default column ordering when the requester does not supply an explicit selection.
     *
     * @return array<int, string>
     */
    public function defaultColumns(): array
    {
        return ['occurred_at', 'action', 'field_name', 'old_value', 'new_value', 'user_name'];
    }

    /**
     * Configure the source query for queued export jobs.
     *
     * @param  array<string, mixed>  $options
     */
    public function query(array $options = []): Builder
    {
        $filters = (array) ($options['filters'] ?? []);
        $productId = (int) ($filters['product_id'] ?? 0);
        unset($filters['product_id']);

        $query = ProductHistory::query()
            ->where('product_id', $productId)
            ->with(['user:id,name,email', 'product:id,name,sku']);

        // Reuse the same list definition to keep filtering semantics aligned
        // with the interactive API endpoint.
        $definition = ProductHistoryListConfiguration::definition();
        $listQuery = ListQueryValidator::fromArray(['filters' => $filters], $definition);
        $listQuery->applyFilters($query);

        if (! $listQuery->hasSort('created_at')) {
            $query->orderByDesc('product_histories.created_at');
        }

        $query->orderByDesc('product_histories.id');

        return $query;
    }

    /**
     * Generate the base filename (without extension) for the export artifact.
     */
    public function fileName(Export $export): string
    {
        $productName = data_get($export->exportable_options, 'meta.product_name', 'product');

        return sprintf('%s-history', str($productName)->slug('-')->toString());
    }

    /**
     * Map the model instance into a simple array of scalar values based on the selected columns.
     *
     * @param  array<string, ExportColumn>  $columns
     * @return array<int, string>
     */
    public function map(Model $model, array $columns): array
    {
        /** @var ProductHistory $model */
        return collect($columns)
            ->map(static fn (ExportColumn $column): string => $column->resolve($model))
            ->values()
            ->all();
    }

    /**
     * Surface the allowed column keys for request validation.
     *
     * @return array<int, string>
     */
    public static function allowedColumnKeys(): array
    {
        return array_keys((new self())->columns());
    }
}
