<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\ProductSimilarities\Pages\CreateProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\EditProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\ListProductSimilarities;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class ProductSimilarityResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema   
    {
        return ProductSimilarityForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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