<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Resources\PriceListItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditPriceListItem extends EditRecord
{
    protected static string $resource = PriceListItemResource::class;

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
                ->label(__('admin.actions.view')),
            Actions\DeleteAction::make()
                ->label(__('admin.actions.delete')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('admin.price_list_items.notifications.updated');
    }
}
