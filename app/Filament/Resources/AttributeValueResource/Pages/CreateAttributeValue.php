<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateAttributeValue
 * 
 * Filament v4 resource for CreateAttributeValue management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateAttributeValue extends CreateRecord
{
    protected static string $resource = AttributeValueResource::class;
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
        // Ensure slug is generated if not provided
        if (empty($data['slug']) && !empty($data['value'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['value']);
        }
        return $data;
    }
}