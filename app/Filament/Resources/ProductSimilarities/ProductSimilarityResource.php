<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductSimilarities\Pages\CreateProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\EditProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\ListProductSimilarities;
use App\Models\ProductSimilarity;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
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
