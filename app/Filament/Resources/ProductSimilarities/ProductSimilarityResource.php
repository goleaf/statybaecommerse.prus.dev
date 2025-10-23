<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\ProductSimilarities\Pages\CreateProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\EditProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\ListProductSimilarities;
use App\Filament\Resources\ProductSimilarities\Schemas\ProductSimilarityForm;
use App\Filament\Resources\ProductSimilarities\Tables\ProductSimilaritiesTable;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ProductSimilarityResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductSimilarity::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
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
