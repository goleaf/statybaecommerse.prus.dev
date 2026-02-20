<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralCodeStatisticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referralCode.code')
                    ->label(__('admin.labels.referral_code'))
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_clicks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_signups')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_conversions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('referral_code_id')
                    ->relationship('referralCode', 'code'),
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
