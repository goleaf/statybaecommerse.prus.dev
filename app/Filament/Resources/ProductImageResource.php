<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\ProductImageResource\Pages;
use App\Models\ProductImage;
use BackedEnum;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Schema;

final class ProductImageResource extends Resource
{
    use HasNav;

    

    

    protected static ?string $model = ProductImage::class;

    protected static ?int $navigationSort = 14;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\FileUpload::make('path')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('product-images')
                    ->visibility('public')
                    ->dehydrateStateUsing(static fn (?string $state): ?string => $state ? 'storage/' . ltrim($state, '/') : null)
                    ->formatStateUsing(static fn (?string $state): ?string => $state && str_starts_with($state, 'storage/') ? substr($state, strlen('storage/')) : $state),
                Forms\Components\TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table|array
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->getStateUsing(static fn (ProductImage $record): ?string => $record->path ? asset(trim($record->path, '/')) : null)
                    ->size(60),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_text')
                    ->limit(30)
                    ->searchable(),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index'  => Pages\ListProductImages::route('/'),
            'create' => Pages\CreateProductImage::route('/create'),
            'edit'   => Pages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
