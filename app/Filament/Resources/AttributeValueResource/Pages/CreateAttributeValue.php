<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Resources\Pages\CreateRecord;

final /**
 * CreateAttributeValue
 * 
 * Filament resource for admin panel management.
 */
class CreateAttributeValue extends CreateRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure slug is generated if not provided
        if (empty($data['slug']) && ! empty($data['value'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['value']);
        }

        return $data;
    }
}
