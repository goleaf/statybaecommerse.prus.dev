<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('translations.variant_name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('sku')
                    ->label(__('translations.variant_sku'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ProductVariant::class, 'sku', ignoreRecord: true),

                Forms\Components\TextInput::make('price')
                    ->label(__('translations.variant_price'))
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),

                Forms\Components\TextInput::make('compare_price')
                    ->label(__('translations.variant_compare_price'))
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),

                Forms\Components\TextInput::make('cost_price')
                    ->label(__('translations.variant_cost_price'))
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),

                Forms\Components\TextInput::make('stock_quantity')
                    ->label(__('translations.variant_stock_quantity'))
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('weight')
                    ->label(__('translations.variant_weight'))
                    ->numeric()
                    ->suffix('kg')
                    ->step(0.01),

                Forms\Components\Toggle::make('is_visible')
                    ->label(__('translations.is_visible'))
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.variant_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('translations.variant_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('translations.variant_price'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('translations.variant_stock'))
                    ->numeric()
                    ->sortable()
                    ->color(fn (ProductVariant $record) => match (true) {
                        $record->stock_quantity <= 0 => 'danger',
                        $record->stock_quantity <= 5 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('weight')
                    ->label(__('translations.variant_weight'))
                    ->suffix(' kg')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('translations.is_visible'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_visible')
                    ->label(__('translations.is_visible'))
                    ->options([
                        true => __('translations.yes'),
                        false => __('translations.no'),
                    ]),

                Tables\Filters\Filter::make('low_stock')
                    ->label(__('translations.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 5)),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('translations.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
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
            ->defaultSort('sort_order');
    }
}
