<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Price;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PriceImporter extends BaseImporter
{
    protected static ?string $model = Price::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('priceable_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('priceable_type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('currency')
                ->relationship(),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('compare_amount')
                ->numeric()
                ->rules(['numeric']),
            ImportColumn::make('type')
                ->rules(['max:255']),
            ImportColumn::make('is_enabled')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): Price
    {
        return new Price;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your price import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'Target'   => ['priceable_id', 'priceable_type'],
            'Pricing'  => ['amount', 'compare_amount', 'cost_amount', 'currency'],
            'Settings' => ['type', 'starts_at', 'ends_at', 'is_enabled'],
        ];
    }
}
