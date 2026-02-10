<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\DiscountRedemption;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.discount_redemption_stats.redeemed');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.number')
                    ->label(__('messages.order_number'))
                    ->url(fn (DiscountRedemption $record) => OrderResource::getUrl('edit', ['record' => $record->order_id]))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('messages.customer'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount'))
                    ->money('EUR')
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
