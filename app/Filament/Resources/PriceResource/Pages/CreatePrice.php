<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePrice extends CreateRecord
{
    protected static string $resource = PriceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['type'] = $data['type'] ?? 'regular';
        $data['valid_from'] = $data['valid_from'] ?? now();
        
        return $data;
    }
}

