<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label(__('customers.order'))
                    ->relationship('order', 'order_number')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('order_number')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => __('orders.statuses.pending'),
                                'processing' => __('orders.statuses.processing'),
                                'shipped' => __('orders.statuses.shipped'),
                                'delivered' => __('orders.statuses.delivered'),
                                'cancelled' => __('orders.statuses.cancelled'),
                                'refunded' => __('orders.statuses.refunded'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
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
            ->recordTitleAttribute('order.order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label(__('customers.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('order.status')
                    ->label(__('customers.status'))
                    ->formatStateUsing(fn (string $state): string => __("orders.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.total')
                    ->label(__('customers.total'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.created_at')
                    ->label(__('customers.order_date'))
                    ->dateTime()
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order')
                    ->label(__('customers.order'))
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('customers.status'))
                    ->options([
                        'pending' => __('orders.statuses.pending'),
                        'processing' => __('orders.statuses.processing'),
                        'shipped' => __('orders.statuses.shipped'),
                        'delivered' => __('orders.statuses.delivered'),
                        'cancelled' => __('orders.statuses.cancelled'),
                        'refunded' => __('orders.statuses.refunded'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order.created_at', 'desc');
    }
}
