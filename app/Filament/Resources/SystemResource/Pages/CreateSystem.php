<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Resources\SystemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSystem extends CreateRecord
{
    protected static string $resource = SystemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Set default values if not provided
        $data['is_required'] ??= false;
        $data['is_public'] ??= false;
        $data['is_readonly'] ??= false;
        $data['is_encrypted'] ??= false;
        $data['cache_ttl'] ??= 3600;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'System setting created successfully';
    }
}
