<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductSimilarityResource\Pages;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class ProductSimilarityResource extends BaseResource
{
    protected static ?string $model = ProductSimilarity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable(),
                Select::make('similar_product_id')
                    ->relationship('similarProduct', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('similarity_score')
                    ->numeric()
                    ->required(),
                TextInput::make('algorithm_type')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarProduct.name')
                    ->label('Similar Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('algorithm_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductSimilarities::route('/'),
            'create' => Pages\CreateProductSimilarity::route('/create'),
            'edit'   => Pages\EditProductSimilarity::route('/{record}/edit'),
        ];
    }
}
