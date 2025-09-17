<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSubscriber extends CreateRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_verified'] = $data['is_verified'] ?? false;
        $data['type'] = $data['type'] ?? 'newsletter';
        $data['subscribed_at'] = $data['subscribed_at'] ?? now();
        
        return $data;
    }
}
