<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('admin.products.edit');
    }

    public function getSubheading(): ?string
    {
        return __('admin.products.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label(__('admin.actions.view')),
            Actions\DeleteAction::make()->label(__('admin.actions.delete')),
        ];
    }
}
