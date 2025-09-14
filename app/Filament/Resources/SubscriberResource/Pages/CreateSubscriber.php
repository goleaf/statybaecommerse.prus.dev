<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

final class CreateSubscriber extends CreateRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Subscriber created successfully')
            ->success()
            ->body('The subscriber has been added to the mailing list.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['subscribed_at'])) {
            $data['subscribed_at'] = now();
        }

        return $data;
    }
}
