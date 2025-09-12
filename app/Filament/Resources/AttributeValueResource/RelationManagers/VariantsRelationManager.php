<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Product Variants';

    protected static ?string $modelLabel = 'Variant';

    protected static ?string $pluralModelLabel = 'Variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('products.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('sku')
                    ->label(__('products.sku'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('products.price'))
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label(__('products.stock_quantity'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('products.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('products.product'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('products.sku'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('products.price'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('products.stock_quantity'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('products.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('products.active'))
                    ->boolean()
                    ->native(false),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('products.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('sku', 'asc');
    }
}
