<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/**
 * EditStock
 * 
 * Filament resource for admin panel management.
 */
class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_view')
                ->label(__('common.back_to_view'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('view', ['record' => $this->getRecord()]))
                ->tooltip(__('common.back_to_view_tooltip')),
            Actions\ViewAction::make()
                ->label(__('inventory.view_stock_item')),
            Actions\DeleteAction::make()
                ->label(__('inventory.delete_stock_item')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('inventory.stock_item_updated');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure numeric values are properly cast
        $data['stock'] = (int) ($data['stock'] ?? 0);
        $data['reserved'] = (int) ($data['reserved'] ?? 0);
        $data['incoming'] = (int) ($data['incoming'] ?? 0);
        $data['threshold'] = (int) ($data['threshold'] ?? 0);
        $data['reorder_point'] = (int) ($data['reorder_point'] ?? 0);
        $data['max_stock_level'] = (int) ($data['max_stock_level'] ?? 0);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        Notification::make()
            ->title(__('inventory.stock_item_updated'))
            ->body(__('inventory.stock_item_updated_message', [
                'product' => $record->display_name,
                'location' => $record->location->name,
            ]))
            ->success()
            ->send();
    }
}
