<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductImageResource\Pages as ImagePages;
use App\Models\ProductImage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

final class ProductImageResource extends BaseResource
{
    protected static ?string $model = ProductImage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.product_images');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.product_images');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.product_image');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('path')
                    ->required()
                    ->maxLength(255),
                TextInput::make('alt_text')
                    ->maxLength(255),
                TextInput::make('sort_order')
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
                ImageColumn::make('url')
                    ->label('Preview'),
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alt_text')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->sortable(),
                ToggleColumn::make('is_active'),
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
            'index'  => ImagePages\ListProductImages::route('/'),
            'create' => ImagePages\CreateProductImage::route('/create'),
            'edit'   => ImagePages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
