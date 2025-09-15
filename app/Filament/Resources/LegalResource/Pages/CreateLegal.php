<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateLegal extends CreateRecord
{
    protected static string $resource = LegalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['valid_from'] = $data['valid_from'] ?? now();
        
        return $data;
    }
}
