<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BrandImporter extends BaseImporter
{
    protected static ?string $model = Brand::class;

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
            ImportColumn::make('website'),
            ImportColumn::make('is_enabled')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('sort_order')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
            ImportColumn::make('customer_group_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('social_links'),
            ImportColumn::make('is_premium')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_featured')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_visible')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('meta_title'),
            ImportColumn::make('meta_description'),
            ImportColumn::make('contact_email')
                ->rules(['email']),
            ImportColumn::make('contact_phone'),
        ];
    }

    public function resolveRecord(): Brand
    {
        return new Brand;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your brand import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'Identification' => ['name', 'slug'],
            'Settings'       => ['is_enabled', 'sort_order', 'is_premium', 'is_featured', 'is_visible', 'is_active'],
            'SEO'            => ['seo_title', 'seo_description', 'meta_title', 'meta_description'],
            'Contact'        => ['website', 'contact_email', 'contact_phone', 'social_links'],
        ];
    }
}
