<?php

declare (strict_types=1);
namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
/**
 * EditAddress
 * 
 * Filament v4 resource for EditAddress management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle getSavedNotification functionality with proper error handling.
     * @return Notification|null
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()->success()->title(__('translations.address_updated'))->body(__('translations.address_updated_successfully'));
    }
    /**
     * Handle mutateFormDataBeforeSave functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            $this->getModel()::where('user_id', $data['user_id'])->where('id', '!=', $this->record->id)->update(['is_default' => false]);
        }
        return $data;
    }
}