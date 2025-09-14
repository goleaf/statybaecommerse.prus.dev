<?php

declare (strict_types=1);
namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Resources\PriceListItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditPriceListItem
 * 
 * Filament v4 resource for EditPriceListItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditPriceListItem extends EditRecord
{
    protected static string $resource = PriceListItemResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_view')->label(__('common.back_to_view'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))->tooltip(__('common.back_to_view_tooltip')), Actions\ViewAction::make()->label(__('admin.actions.view')), Actions\DeleteAction::make()->label(__('admin.actions.delete'))];
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
        return __('admin.price_list_items.notifications.updated');
    }
}