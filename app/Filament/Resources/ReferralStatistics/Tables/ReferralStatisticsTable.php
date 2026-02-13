<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completed_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pending_referrals')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rewards_earned')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total_discounts_given')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
