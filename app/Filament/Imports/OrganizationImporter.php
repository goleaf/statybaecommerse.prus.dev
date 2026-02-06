<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Organization;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class OrganizationImporter extends BaseImporter
{
    protected static ?string $model = Organization::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('type')
                ->rules(['max:255']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('settings'),
        ];
    }

    public function resolveRecord(): Organization
    {
        return new Organization;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your organization import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = static::calculateFailedRowsCount($import)) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'General'  => ['name', 'slug', 'type'],
            'Settings' => ['is_active'],
        ];
    }
}
