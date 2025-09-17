<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['status'] = $data['status'] ?? 'draft';
        $data['is_scheduled'] = $data['is_scheduled'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        return $data;
    }
}
