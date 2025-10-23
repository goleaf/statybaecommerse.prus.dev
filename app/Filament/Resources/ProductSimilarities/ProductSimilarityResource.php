<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
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

class ProductSimilarityResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductSimilarity::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return ProductSimilarityForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
