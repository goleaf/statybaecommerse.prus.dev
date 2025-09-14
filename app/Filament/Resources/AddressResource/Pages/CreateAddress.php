<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

final /**
 * CreateAddress
 * 
 * Filament resource for admin panel management.
 */
class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('translations.address_created'))
            ->body(__('translations.address_created_successfully'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            $this->getModel()::where('user_id', $data['user_id'])
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
