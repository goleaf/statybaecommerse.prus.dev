<?php

namespace App\Filament\Imports;

use App\Models\Price;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PriceImporter extends Importer
{
    protected static ?string $model = Price::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }

    public function resolveRecord(): Price
    {
        return new Price();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your price import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
