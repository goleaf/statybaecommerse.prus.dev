<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\Schemas\CategoryForm;
use App\Filament\Resources\CategoryResource\Schemas\CategoryInfolist;
use App\Filament\Resources\CategoryResource\Tables\CategoriesTable;
use App\Models\Category;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class CategoryResource extends BaseResource
{
    use HasNav;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    public static function getRecordTitleAttribute(): ?string
    {
        return 'name';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.categories.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.categories.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.categories.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \App\Models\Scopes\ActiveScope::class,
                \App\Models\Scopes\EnabledScope::class,
                \App\Models\Scopes\VisibleScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CategoryResource\RelationManagers\ProductsRelationManager::class,
            \App\Filament\Resources\CategoryResource\RelationManagers\ProductVariantsRelationManager::class,
            \App\Filament\Resources\CategoryResource\RelationManagers\CollectionsRelationManager::class,
            \App\Filament\Resources\CategoryResource\RelationManagers\OrdersRelationManager::class,
            \App\Filament\Resources\CategoryResource\RelationManagers\SubcategoriesRelationManager::class,
            \App\Filament\Resources\CategoryResource\RelationManagers\DiscountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\CategoryTree::route('/'),
            'list'   => Pages\ListCategories::route('/list'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}'),
        ];
    }
}
