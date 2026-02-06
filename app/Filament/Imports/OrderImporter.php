<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Order;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class OrderImporter extends BaseImporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('number')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('user')
                ->relationship(),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('currency')
                ->rules(['max:255']),
            ImportColumn::make('billing_address'),
            ImportColumn::make('shipping_address'),
            ImportColumn::make('payment_status')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): Order
    {
        return new Order;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your order import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = static::calculateFailedRowsCount($import)) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'General'   => ['number', 'user', 'status'],
            'Financial' => ['total', 'currency', 'payment_status'],
            'Address'   => ['billing_address', 'shipping_address'],
        ];
    }
}
