<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('admin.orders.edit');
    }

    public function getSubheading(): ?string
    {
        return __('admin.orders.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label(__('admin.actions.view')),
            Actions\DeleteAction::make()->label(__('admin.actions.delete')),
        ];
    }
}
