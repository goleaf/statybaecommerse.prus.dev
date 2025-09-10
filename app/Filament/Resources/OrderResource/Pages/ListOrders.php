<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('admin.orders.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.orders.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('admin.actions.create')),
        ];
    }
}
