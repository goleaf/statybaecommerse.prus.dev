<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditCountry
 * 
 * Filament v4 resource for EditCountry management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make(), Actions\RestoreAction::make(), Actions\ForceDeleteAction::make()];
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
     * Handle getSavedNotificationTitle functionality with proper error handling.
     * @return string|null
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.countries.messages.updated');
    }
}