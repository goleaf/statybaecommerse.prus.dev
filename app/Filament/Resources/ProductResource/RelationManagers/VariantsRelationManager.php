<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Variant Information')
                    ->components([
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(255)
                            ->unique('product_variants', 'sku', ignoreRecord: true),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('compare_price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Inventory')
                    ->components([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->numeric()
                            ->default(10)
                            ->minValue(0),
                        Forms\Components\Toggle::make('track_inventory')
                            ->default(true),
                        Forms\Components\Toggle::make('is_default')
                            ->default(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Physical Properties')
                    ->components([
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01),
                        Forms\Components\TextInput::make('length')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                        Forms\Components\TextInput::make('width')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                        Forms\Components\TextInput::make('height')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01),
                    ])
                    ->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl('/images/placeholder-variant.jpg')
                    ->circular(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('track_inventory')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->where('stock_quantity', '<=', 10)),
                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn(Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('is_default', 'desc');
    }
}
