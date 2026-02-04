<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Subscriber;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SubscriberImporter extends BaseImporter
{
    protected static ?string $model = Subscriber::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('first_name')
                ->rules(['max:255']),
            ImportColumn::make('last_name')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->rules(['max:255']),
            ImportColumn::make('company')
                ->rules(['max:255']),
            ImportColumn::make('job_title')
                ->rules(['max:255']),
            ImportColumn::make('source')
                ->rules(['max:255']),
            ImportColumn::make('status')
                ->rules(['max:255']),
            ImportColumn::make('is_verified')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('accepts_marketing')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('newsletter_subscription')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): Subscriber
    {
        return new Subscriber;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your subscriber import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'General'  => ['email', 'first_name', 'last_name', 'phone'],
            'Business' => ['company', 'job_title'],
            'Status'   => ['status', 'is_verified', 'accepts_marketing'],
        ];
    }
}
