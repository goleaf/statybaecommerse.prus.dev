<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListProducts extends ListRecords
{
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->label(__('translations.import') . ' CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(ProductImporter::class),
        ];
    }
}
