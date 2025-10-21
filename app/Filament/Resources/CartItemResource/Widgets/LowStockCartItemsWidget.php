<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Widgets;

use App\Models\CartItem;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class LowStockCartItemsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Cart Items';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CartItem::query()
                    ->whereHas('product.inventories', function ($query) {
                        $query->where('quantity', '<=', 10);
                    })
                    ->with(['product', 'user'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('cart_items.user'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('cart_items.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label(__('cart_items.sku'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('cart_items.quantity'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('product.inventories.quantity')
                    ->label(__('cart_items.stock_quantity'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        $state <= 10 => 'info',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('cart_items.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('cart_items.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->label(__('cart_items.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product')
                    ->label(__('cart_items.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('cart_items.is_active'))
                    ->boolean()
                    ->trueLabel(__('cart_items.active_only'))
                    ->falseLabel(__('cart_items.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
