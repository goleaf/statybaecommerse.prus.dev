<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;
        $data['type'] = $data['type'] ?? 'shipping';

        return $data;
    }
}
