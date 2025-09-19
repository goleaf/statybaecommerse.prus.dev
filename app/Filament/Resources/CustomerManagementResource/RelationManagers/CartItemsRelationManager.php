<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\CartItem;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    protected static ?string $title = 'Cart Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cart_item_id')
                    ->label(__('customers.cart_item'))
                    ->relationship('cartItem', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_saved_for_later')
                            ->default(false),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cartItem.id')
            ->columns([
                Tables\Columns\TextColumn::make('cartItem.product.name')
                    ->label(__('customers.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('cartItem.product.sku')
                    ->label(__('customers.sku'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('cartItem.quantity')
                    ->label(__('customers.quantity'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('cartItem.product.price')
                    ->label(__('customers.price'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cartItem.total_price')
                    ->label(__('customers.total_price'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('cartItem.is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('cartItem.is_saved_for_later')
                    ->label(__('customers.saved_for_later'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('cartItem.created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cartItem')
                    ->label(__('customers.cart_item'))
                    ->relationship('cartItem', 'id')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('product')
                    ->label(__('customers.product'))
                    ->relationship('cartItem.product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_saved_for_later')
                    ->label(__('customers.saved_for_later'))
                    ->boolean()
                    ->trueLabel(__('customers.saved_only'))
                    ->falseLabel(__('customers.active_cart_only'))
                    ->native(false),
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
