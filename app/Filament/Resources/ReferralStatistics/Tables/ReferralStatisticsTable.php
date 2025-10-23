<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_discounts_given')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
