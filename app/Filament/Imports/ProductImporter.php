<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    protected function beforeValidate(): void
    {
        $name = $this->data['name'] ?? null;

        if (($this->data['slug'] ?? null) === null && is_string($name) && $name !== '') {
            $this->data['slug'] = Str::slug($name);
        }
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('slug')
                ->rules(['required', 'string']),
            ImportColumn::make('description'),
            ImportColumn::make('short_description'),
            ImportColumn::make('sku')
                ->label('SKU')
                ->rules(['nullable', 'string']),
            ImportColumn::make('summary'),
            ImportColumn::make('price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('sale_price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('compare_price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('cost_price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('manage_stock')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('stock_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('low_stock_threshold')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('weight')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('length')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('width')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('height')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('is_visible')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_enabled')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_featured')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
            ImportColumn::make('brand')
                ->relationship()
                ->ignoreBlankState(),
            ImportColumn::make('type')
                ->ignoreBlankState()
                ->rules(['nullable', 'in:simple,variable']),
            ImportColumn::make('is_requestable')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('requests_count')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('minimum_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('hide_add_to_cart')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('request_message'),
            ImportColumn::make('meta_title'),
            ImportColumn::make('meta_description'),
            ImportColumn::make('meta_keywords'),
            ImportColumn::make('barcode'),
            ImportColumn::make('track_inventory')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('metadata'),
            ImportColumn::make('video_url'),
            ImportColumn::make('view_count')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('last_viewed_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('track_stock')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('allow_backorder')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('sort_order')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('tax_class'),
            ImportColumn::make('shipping_class'),
            ImportColumn::make('download_limit')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('download_expiry')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('external_url'),
            ImportColumn::make('button_text'),
            ImportColumn::make('gallery'),
            ImportColumn::make('available_from')
                ->rules(['nullable', 'date']),
            ImportColumn::make('available_until')
                ->rules(['nullable', 'date']),
            ImportColumn::make('warehouse_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('views_count')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('status')
                ->ignoreBlankState()
                ->rules(['nullable', 'in:draft,published,archived']),
        ];
    }

    public function resolveRecord(): Product
    {
        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
