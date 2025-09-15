<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSeoData extends CreateRecord
{
    protected static string $resource = SeoDataResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_indexed'] = $data['is_indexed'] ?? true;
        $data['is_canonical'] = $data['is_canonical'] ?? false;
        $data['priority'] = $data['priority'] ?? 0.5;
        $data['change_frequency'] = $data['change_frequency'] ?? 'weekly';
        
        return $data;
    }
}
