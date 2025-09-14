<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateCountry
 * 
 * Filament v4 resource for CreateCountry management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle getCreatedNotificationTitle functionality with proper error handling.
     * @return string|null
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.countries.messages.created');
    }
}