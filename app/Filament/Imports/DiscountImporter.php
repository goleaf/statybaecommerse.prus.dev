<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Discount;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DiscountImporter extends BaseImporter
{
    protected static ?string $model = Discount::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('value')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('starts_at')
                ->rules(['datetime']),
            ImportColumn::make('ends_at')
                ->rules(['datetime']),
            ImportColumn::make('usage_limit')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public function resolveRecord(): Discount
    {
        return new Discount;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your discount import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = static::calculateFailedRowsCount($import)) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'General' => ['name', 'slug', 'description', 'type', 'value'],
            'Rules'   => ['starts_at', 'ends_at', 'usage_limit', 'minimum_amount', 'maximum_amount'],
            'Status'  => ['is_active', 'is_enabled', 'status', 'priority'],
        ];
    }
}
