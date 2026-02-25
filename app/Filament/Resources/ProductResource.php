<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CollectionsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\FeaturesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\InventoryRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\PricesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\RequestsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\SimilaritiesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantCombinationsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\ProductResource\Schemas\ProductForm;
use App\Filament\Resources\ProductResource\Tables\ProductsTable;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use BackedEnum;
use Closure;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

final class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordRouteKeyName = 'id';

    public static function getNavigationLabel(): string
    {
        return __('admin.products.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.products.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.products.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
                PublishedScope::class,
                VisibleScope::class,
            ]);
    }

    public static function resolveRecordRouteBinding(int|string $key, ?Closure $modifyQuery = null): ?Model
    {
        $query = self::getRecordRouteBindingEloquentQuery();

        if ($modifyQuery) {
            $query = $modifyQuery($query) ?? $query;
        }

        $record = (clone $query)
            ->whereKey($key)
            ->first();

        if ($record instanceof Model) {
            return $record;
        }

        return (clone $query)
            ->where('slug', (string) $key)
            ->first();
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        $relations = [
            VariantsRelationManager::class,
            VariantCombinationsRelationManager::class,
            PricesRelationManager::class,
            ImagesRelationManager::class,
            FeaturesRelationManager::class,
            RequestsRelationManager::class,
            OrdersRelationManager::class,
            SimilaritiesRelationManager::class,
            InventoryRelationManager::class,
            CategoriesRelationManager::class,
            CollectionsRelationManager::class,
            CommentsRelationManager::class,
        ];

        if (SchemaFacade::hasTable('discounts') && SchemaFacade::hasTable('discount_products')) {
            $relations[] = DiscountsRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
