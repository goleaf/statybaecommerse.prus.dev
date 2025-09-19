<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use App\Models\Product;
use App\Models\PriceListItem;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Price List Items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_id')
                    ->label(__('price_list_items.product'))
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('sku')
                            ->maxLength(255),
                    ]),

                Forms\Components\TextInput::make('price')
                    ->label(__('price_list_items.price'))
                    ->numeric()
                    ->required()
                    ->prefix('€')
                    ->minValue(0)
                    ->step(0.01),

                Forms\Components\TextInput::make('discount_percentage')
                    ->label(__('price_list_items.discount_percentage'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->suffix('%'),

                Forms\Components\TextInput::make('min_quantity')
                    ->label(__('price_list_items.min_quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1),

                Forms\Components\TextInput::make('max_quantity')
                    ->label(__('price_list_items.max_quantity'))
                    ->numeric()
                    ->minValue(1),

                Forms\Components\DateTimePicker::make('valid_from')
                    ->label(__('price_list_items.valid_from'))
                    ->default(now()),

                Forms\Components\DateTimePicker::make('valid_until')
                    ->label(__('price_list_items.valid_until'))
                    ->after('valid_from'),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('price_list_items.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('price_list_items.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label(__('price_list_items.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('price_list_items.price'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('price_list_items.discount_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state ? "{$state}%" : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_quantity')
                    ->label(__('price_list_items.min_quantity'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_quantity')
                    ->label(__('price_list_items.max_quantity'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('price_list_items.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('price_list_items.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('price_list_items.sort_order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_list_items.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->label(__('price_list_items.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('price_list_items.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_list_items.active_only'))
                    ->falseLabel(__('price_list_items.inactive_only'))
                    ->native(false),

                Tables\Filters\Filter::make('valid_now')
                    ->label(__('price_list_items.valid_now'))
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query) {
                        $query->where('valid_from', '<=', now())
                            ->where(function (Builder $query) {
                                $query->whereNull('valid_until')
                                    ->orWhere('valid_until', '>=', now());
                            });
                    })),

                Tables\Filters\Filter::make('expired')
                    ->label(__('price_list_items.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now())),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
