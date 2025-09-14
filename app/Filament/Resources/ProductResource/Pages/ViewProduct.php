<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
/**
 * ViewProduct
 * 
 * Filament v4 resource for ViewProduct management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_list')->label(__('common.back_to_list'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('index'))->tooltip(__('common.back_to_list_tooltip')), Actions\EditAction::make(), Actions\DeleteAction::make()];
    }
}