<?php

declare (strict_types=1);
namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
/**
 * ViewLocation
 * 
 * Filament v4 resource for ViewLocation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ViewLocation extends ViewRecord
{
    protected static string $resource = LocationResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_list')->label(__('common.back_to_list'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('index'))->tooltip(__('common.back_to_list_tooltip')), Actions\EditAction::make(), Actions\DeleteAction::make()];
    }
}