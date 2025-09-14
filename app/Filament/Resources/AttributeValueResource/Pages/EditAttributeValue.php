<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditAttributeValue
 * 
 * Filament v4 resource for EditAttributeValue management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditAttributeValue extends EditRecord
{
    protected static string $resource = AttributeValueResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make(), Actions\RestoreAction::make(), Actions\ForceDeleteAction::make()];
    }
    /**
     * Handle mutateFormDataBeforeSave functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure slug is generated if not provided
        if (empty($data['slug']) && !empty($data['value'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['value']);
        }
        return $data;
    }
}