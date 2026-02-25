<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockReservations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.inventory.stock_reservations');
    }

    public function table(Table $table): Table
    {
        // Guessing the relationship name might be 'stockReservations' based on common patterns.
        // I should define it in Inventory model if it's missing.
        return $table
            ->columns([
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('messages.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
