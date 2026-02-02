<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Imports\BrandImporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\ProductImporter;
use BackedEnum;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;

class ImportData extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected string $view = 'filament.pages.import-data';

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('importProducts')
                ->label('Import Products')
                ->importer(ProductImporter::class),
            ImportAction::make('importBrands')
                ->label('Import Brands')
                ->importer(BrandImporter::class),
            ImportAction::make('importCategories')
                ->label('Import Categories')
                ->importer(CategoryImporter::class),
        ];
    }
}
