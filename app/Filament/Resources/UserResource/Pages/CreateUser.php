<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('users.messages.created_successfully'))
            ->body(__('users.messages.created_successfully_description'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default password if not provided
        if (empty($data['password'])) {
            $data['password'] = bcrypt('password123');
        }

        // Set email verification if not provided
        if (!isset($data['email_verified_at'])) {
            $data['email_verified_at'] = now();
        }

        return $data;
    }
}
