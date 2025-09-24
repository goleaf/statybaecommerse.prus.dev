<?php

namespace App\Filament\Resources\ProductComparisons;

use App\Filament\Resources\ProductComparisons\Pages\CreateProductComparison;
use App\Filament\Resources\ProductComparisons\Pages\EditProductComparison;
use App\Filament\Resources\ProductComparisons\Pages\ListProductComparisons;
use App\Filament\Resources\ProductComparisons\Schemas\ProductComparisonForm;
use App\Filament\Resources\ProductComparisons\Tables\ProductComparisonsTable;
use App\Models\ProductComparison;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ProductComparisonResource extends Resource
{
    protected static ?string $model = ProductComparison::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }

    public static function form(Schema $schema): Schema
    {
        return ProductComparisonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductComparisonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductComparisons::route('/'),
            'create' => CreateProductComparison::route('/create'),
            'edit' => EditProductComparison::route('/{record}/edit'),
        ];
    }
}
