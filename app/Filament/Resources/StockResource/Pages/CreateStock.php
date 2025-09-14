<?php

declare (strict_types=1);
namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateStock
 * 
 * Filament v4 resource for CreateStock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;
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
        return __('inventory.stock_item_created');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['stock'] = $data['stock'] ?? 0;
        $data['reserved'] = $data['reserved'] ?? 0;
        $data['incoming'] = $data['incoming'] ?? 0;
        $data['threshold'] = $data['threshold'] ?? 0;
        $data['reorder_point'] = $data['reorder_point'] ?? 0;
        $data['is_tracked'] = $data['is_tracked'] ?? true;
        $data['status'] = $data['status'] ?? 'active';
        return $data;
    }
    /**
     * Handle afterCreate functionality with proper error handling.
     * @return void
     */
    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        // Create initial stock movement if stock is greater than 0
        if ($record->stock > 0) {
            $record->stockMovements()->create(['quantity' => $record->stock, 'type' => 'in', 'reason' => 'manual_adjustment', 'reference' => 'initial_stock', 'notes' => __('inventory.initial_stock_creation'), 'user_id' => auth()->id(), 'moved_at' => now()]);
        }
        Notification::make()->title(__('inventory.stock_item_created'))->body(__('inventory.stock_item_created_message', ['product' => $record->display_name, 'location' => $record->location->name, 'stock' => $record->stock]))->success()->send();
    }
}