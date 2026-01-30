<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\Schemas\BrandForm;
use App\Filament\Resources\BrandResource\Schemas\BrandInfolist;
use App\Models\Brand;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class BrandResource extends BaseResource
{
    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

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
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label(__('messages.image'))
                    ->collection('logo')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.brands.is_active')),
                TextColumn::make('products_count')
                    ->label(__('admin.brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.brands.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager::class,
        ];
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
