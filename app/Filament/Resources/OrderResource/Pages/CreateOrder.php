<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * CreateOrder
 * 
 * Filament resource for admin panel management.
 */
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number if not provided
        if (empty($data['number'])) {
            $data['number'] = 'ORD-'.strtoupper(uniqid());
        }

        // Set default currency
        if (empty($data['currency'])) {
            $data['currency'] = 'EUR';
        }

        return $data;
    }
}
