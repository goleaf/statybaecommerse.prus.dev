<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

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
            ImportColumn::make('parent')
                ->relationship(),
            ImportColumn::make('sort_order')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('is_visible')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_active')
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
            ImportColumn::make('color'),
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
            ImportColumn::make('show_in_menu')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('product_limit')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('meta_title'),
            ImportColumn::make('meta_description'),
            ImportColumn::make('icon'),
        ];
    }

    public function resolveRecord(): Category
    {
        return new Category();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your category import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
