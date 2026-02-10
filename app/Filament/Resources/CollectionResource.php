<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Filament\Resources\CollectionResource\Schemas\CollectionForm;
use App\Filament\Resources\CollectionResource\Tables\CollectionsTable;
use App\Models\Collection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.collections');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.collections');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.collection');
    }

    public static function form(Schema $schema): Schema
    {
        return CollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CollectionResource\RelationManagers\ProductsRelationManager::class,
            \App\Filament\Resources\CollectionResource\RelationManagers\CategoriesRelationManager::class,
            \App\Filament\Resources\CollectionResource\RelationManagers\BrandsRelationManager::class,
            \App\Filament\Resources\CollectionResource\RelationManagers\PricesRelationManager::class,
            \App\Filament\Resources\CollectionResource\RelationManagers\DiscountsRelationManager::class,
            \App\Filament\Resources\CollectionResource\RelationManagers\RulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}