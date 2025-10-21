<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;

use App\Filament\Resources\ProductSimilarities\Pages\CreateProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\EditProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\ListProductSimilarities;
use App\Filament\Resources\ProductSimilarities\Schemas\ProductSimilarityForm;
use App\Filament\Resources\ProductSimilarities\Tables\ProductSimilaritiesTable;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductSimilarityResource extends Resource
{
    protected static ?string $model = ProductSimilarity::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductSimilarityForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ProductSimilaritiesTable::configure($table);
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
            'index'  => ListProductSimilarities::route('/'),
            'create' => CreateProductSimilarity::route('/create'),
            'edit'   => EditProductSimilarity::route('/{record}/edit'),
        ];
    }
}
