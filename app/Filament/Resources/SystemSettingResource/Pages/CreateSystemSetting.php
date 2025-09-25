<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['name'] = $data['name'] ?? $data['key'] ?? 'setting';
        $data['group'] = $data['group'] ?? 'general';
        $data['cache_ttl'] = $data['cache_ttl'] ?? 3600;
        $data['environment'] = $data['environment'] ?? 'all';
        $data['is_public'] = $data['is_public'] ?? false;
        $data['is_required'] = $data['is_required'] ?? false;
        $data['is_encrypted'] = $data['is_encrypted'] ?? false;
        $data['is_readonly'] = $data['is_readonly'] ?? false;

        return $data;
    }
}
