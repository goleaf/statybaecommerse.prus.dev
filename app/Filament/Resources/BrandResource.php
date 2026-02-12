<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\Schemas\BrandForm;
use App\Filament\Resources\BrandResource\Schemas\BrandInfolist;
use App\Filament\Resources\BrandResource\Tables\BrandsTable;
use App\Models\Brand;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

final class BrandResource extends BaseResource
{
    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.brands');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.brands.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.brands.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return BrandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BrandInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \App\Models\Scopes\ActiveScope::class,
                \App\Models\Scopes\EnabledScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return BrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        $relations = [
            \App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager::class,
            \App\Filament\Resources\BrandResource\RelationManagers\CategoriesRelationManager::class,
            \App\Filament\Resources\BrandResource\RelationManagers\CollectionsRelationManager::class,
            \App\Filament\Resources\BrandResource\RelationManagers\OrdersRelationManager::class,
            \App\Filament\Resources\BrandResource\RelationManagers\ProductVariantsRelationManager::class,
        ];

        if (SchemaFacade::hasTable('discounts') && SchemaFacade::hasTable('discount_brands')) {
            $relations[] = \App\Filament\Resources\BrandResource\RelationManagers\DiscountsRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
