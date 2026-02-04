<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Partner;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PartnerImporter extends BaseImporter
{
    protected static ?string $model = Partner::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('tier')
                ->relationship(),
            ImportColumn::make('user')
                ->relationship(),
            ImportColumn::make('contact_email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('contact_phone')
                ->rules(['max:255']),
            ImportColumn::make('is_enabled')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('discount_rate')
                ->numeric()
                ->rules(['numeric', 'min:0', 'max:100']),
            ImportColumn::make('commission_rate')
                ->numeric()
                ->rules(['numeric', 'min:0', 'max:100']),
            ImportColumn::make('metadata'),
        ];
    }

    public function resolveRecord(): Partner
    {
        return new Partner;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your partner import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'General'  => ['name', 'code', 'tier'],
            'Contact'  => ['contact_email', 'contact_phone'],
            'Settings' => ['is_enabled', 'discount_rate', 'commission_rate'],
        ];
    }
}
