<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('admin.products.view');
    }

    public function getSubheading(): ?string
    {
        return __('admin.products.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label(__('admin.actions.edit')),
        ];
    }
}
