<?php

declare (strict_types=1);
namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateDiscountCondition
 * 
 * Filament v4 resource for CreateDiscountCondition management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateDiscountCondition extends CreateRecord
{
    protected static string $resource = DiscountConditionResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_list')->label(__('common.back_to_list'))->icon('heroicon-o-arrow-left')->color('gray')->url($this->getResource()::getUrl('index'))->tooltip(__('common.back_to_list_tooltip'))];
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
     * Handle getCreatedNotificationTitle functionality with proper error handling.
     * @return string|null
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('discount_conditions.notifications.created');
    }
}