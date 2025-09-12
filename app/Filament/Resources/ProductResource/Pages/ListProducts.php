<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('admin.products.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.products.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('admin.actions.create')),
        ];
    }
}
