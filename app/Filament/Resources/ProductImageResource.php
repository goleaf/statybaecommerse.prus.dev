<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductImageResource\Pages;
use App\Models\ProductImage;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class ProductImageResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Products';
    }

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-photo';
    }

    protected static ?string $model = ProductImage::class;

    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\FileUpload::make('path')
                    ->label('Image')
                    ->image()
                    ->directory('product-images')
                    ->visibility('public'),
                Forms\Components\TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->size(60),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_text')
                    ->limit(30),
                Tables\Columns\TextColumn::make('sort_order'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListProductImages::route('/'),
            'create' => Pages\CreateProductImage::route('/create'),
            'edit' => Pages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
