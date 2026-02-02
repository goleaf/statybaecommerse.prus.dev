<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('description'),
            ImportColumn::make('short_description'),
            ImportColumn::make('sku')
                ->label('SKU'),
            ImportColumn::make('summary'),
            ImportColumn::make('price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('sale_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('compare_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('cost_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('manage_stock')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('stock_quantity')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('low_stock_threshold')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('weight')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('length')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('width')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('height')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('is_visible')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_enabled')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_featured')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('published_at')
                ->rules(['datetime']),
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
            ImportColumn::make('brand')
                ->relationship(),
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('is_requestable')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('requests_count')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('minimum_quantity')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('hide_add_to_cart')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('request_message'),
            ImportColumn::make('meta_title'),
            ImportColumn::make('meta_description'),
            ImportColumn::make('meta_keywords'),
            ImportColumn::make('barcode'),
            ImportColumn::make('track_inventory')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('metadata'),
            ImportColumn::make('video_url'),
            ImportColumn::make('view_count')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('last_viewed_at')
                ->rules(['datetime']),
            ImportColumn::make('track_stock')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('allow_backorder')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('sort_order')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('tax_class'),
            ImportColumn::make('shipping_class'),
            ImportColumn::make('download_limit')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('download_expiry')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('external_url'),
            ImportColumn::make('button_text'),
            ImportColumn::make('gallery'),
            ImportColumn::make('available_from')
                ->rules(['datetime']),
            ImportColumn::make('available_until')
                ->rules(['datetime']),
            ImportColumn::make('warehouse_quantity')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('views_count')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): Product
    {
        return new Product();
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
