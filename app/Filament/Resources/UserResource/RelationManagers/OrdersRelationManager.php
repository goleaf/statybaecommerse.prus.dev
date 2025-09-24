<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('orders.fields.number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('grand_total_amount')
                    ->label(__('orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->label(__('orders.fields.currency'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
