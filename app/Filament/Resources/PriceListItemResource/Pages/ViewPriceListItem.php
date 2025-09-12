<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Resources\PriceListItemResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewPriceListItem extends ViewRecord
{
    protected static string $resource = PriceListItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
            Actions\EditAction::make()
                ->label(__('admin.actions.edit')),
            Actions\DeleteAction::make()
                ->label(__('admin.actions.delete')),
        ];
    }
}
