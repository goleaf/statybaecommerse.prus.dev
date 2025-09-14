<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final /**
 * EditAddress
 * 
 * Filament resource for admin panel management.
 */
class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('translations.address_updated'))
            ->body(__('translations.address_updated_successfully'));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            $this->getModel()::where('user_id', $data['user_id'])
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
