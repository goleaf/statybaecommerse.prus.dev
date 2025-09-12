<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('admin.orders.view');
    }

    public function getSubheading(): ?string
    {
        return __('admin.orders.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label(__('admin.actions.edit')),
        ];
    }
}
