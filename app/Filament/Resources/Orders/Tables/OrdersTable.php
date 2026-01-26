<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('orders.fields.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('orders.fields.customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('orders.status.pending'),
                        'processing' => __('orders.status.processing'),
                        'shipped' => __('orders.status.shipped'),
                        'delivered' => __('orders.status.delivered'),
                        'cancelled' => __('orders.status.cancelled'),
                        'refunded' => __('orders.status.refunded'),
                        'returned' => __('orders.status.returned'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled', 'refunded', 'returned' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money(fn ($record) => $record->currency ?? 'EUR')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('orders.fields.items_count'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('orders.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.fields.status'))
                    ->options([
                        'pending' => __('orders.status.pending'),
                        'processing' => __('orders.status.processing'),
                        'shipped' => __('orders.status.shipped'),
                        'delivered' => __('orders.status.delivered'),
                        'cancelled' => __('orders.status.cancelled'),
                        'refunded' => __('orders.status.refunded'),
                        'returned' => __('orders.status.returned'),
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
