<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BaseResource;
use App\Models\ProductFeature;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ProductFeatureResource extends BaseResource
{
    protected static ?string $model = ProductFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('feature_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('feature_key')
                    ->required()
                    ->maxLength(255),
                TextInput::make('feature_value')
                    ->required()
                    ->maxLength(255),
                TextInput::make('weight')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
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
            'index' => Pages\ListProductFeatures::route('/'),
            'create' => Pages\CreateProductFeature::route('/create'),
            'edit' => Pages\EditProductFeature::route('/{record}/edit'),
        ];
    }
}
