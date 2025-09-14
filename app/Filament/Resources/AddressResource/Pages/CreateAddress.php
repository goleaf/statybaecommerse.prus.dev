<?php

declare (strict_types=1);
namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
/**
 * CreateAddress
 * 
 * Filament v4 resource for CreateAddress management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle getCreatedNotification functionality with proper error handling.
     * @return Notification|null
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()->success()->title(__('translations.address_created'))->body(__('translations.address_created_successfully'));
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            $this->getModel()::where('user_id', $data['user_id'])->update(['is_default' => false]);
        }
        return $data;
    }
}