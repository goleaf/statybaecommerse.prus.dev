<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAttributeValue extends CreateRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
