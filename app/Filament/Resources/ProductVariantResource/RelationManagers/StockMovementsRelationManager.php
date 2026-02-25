<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.inventory_management.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
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
