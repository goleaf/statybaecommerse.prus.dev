<?php

declare(strict_types=1);

namespace App\Services\Export\Exporters;

use App\Models\Export;
use App\Models\Product;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\ExportColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

final class ProductExport implements Exportable
{
    public function name(): string
    {
        return __('Products Export');
    }

    public function columns(): array
    {
        return [
            'sku' => new ExportColumn('sku', __('products.fields.sku'), 'sku'),
            'name' => new ExportColumn('name', __('products.fields.name'), 'name'),
            'price' => new ExportColumn('price', __('products.fields.price'), resolver: fn (Product $product): string => Number::currency((float) $product->price, 'EUR')),
            'stock_quantity' => new ExportColumn('stock_quantity', __('products.fields.stock_quantity'), 'stock_quantity'),
            'status' => new ExportColumn('status', __('products.fields.status'), 'status'),
            'is_visible' => new ExportColumn('is_visible', __('products.fields.is_visible'), 'is_visible'),
            'published_at' => new ExportColumn('published_at', __('products.fields.published_at'), 'published_at'),
        ];
    }

    public function defaultColumns(): array
    {
        return ['sku', 'name', 'price', 'stock_quantity'];
    }

    public function query(array $options = []): Builder
    {
        return Product::query();
    }

    public function fileName(Export $export): string
    {
        return 'products-export';
    }

    public function map(Model $model, array $columns): array
    {
        /** @var Product $model */
        return collect($columns)
            ->map(fn (ExportColumn $column): string => $column->resolve($model))
            ->values()
            ->all();
    }
}
