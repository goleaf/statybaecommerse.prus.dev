<?php

declare (strict_types=1);
namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreatePrice
 * 
 * Filament v4 resource for CreatePrice management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreatePrice extends CreateRecord
{
    protected static string $resource = PriceResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle translations
        if (isset($data['translations'])) {
            $translations = $data['translations'];
            unset($data['translations']);
        }
        return $data;
    }
    /**
     * Handle afterCreate functionality with proper error handling.
     * @return void
     */
    protected function afterCreate(): void
    {
        // Save translations after creating the price
        if (isset($this->data['translations'])) {
            $translations = $this->data['translations'];
            foreach ($translations as $locale => $translationData) {
                if (!empty(array_filter($translationData))) {
                    $this->record->updateTranslation($locale, $translationData);
                }
            }
        }
    }
}