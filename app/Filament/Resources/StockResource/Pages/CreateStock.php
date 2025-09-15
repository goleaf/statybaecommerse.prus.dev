<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['quantity'] = $data['quantity'] ?? 0;
        $data['reserved_quantity'] = $data['reserved_quantity'] ?? 0;
        $data['min_quantity'] = $data['min_quantity'] ?? 0;
        $data['max_quantity'] = $data['max_quantity'] ?? 1000;
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $data;
    }
}
