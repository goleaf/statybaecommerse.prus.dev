<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductFeatureResource\Pages as FeaturePages;
use App\Filament\Resources\ProductFeatureResource\Schemas\ProductFeatureForm;
use App\Filament\Resources\ProductFeatureResource\Schemas\ProductFeatureInfolist;
use App\Models\ProductFeature;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class ProductFeatureResource extends BaseResource
{
    protected static ?string $model = ProductFeature::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function form(Schema $schema): Schema
    {
        return ProductFeatureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductFeatureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_key')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_value')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('weight')
                    ->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name'),
                SelectFilter::make('feature_type')
                    ->options(fn () => ProductFeature::distinct()->pluck('feature_type', 'feature_type')->toArray()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => FeaturePages\ListProductFeatures::route('/'),
            'create' => FeaturePages\CreateProductFeature::route('/create'),
            'view'   => FeaturePages\ViewProductFeature::route('/{record}'),
            'edit'   => FeaturePages\EditProductFeature::route('/{record}/edit'),
        ];
    }
}
