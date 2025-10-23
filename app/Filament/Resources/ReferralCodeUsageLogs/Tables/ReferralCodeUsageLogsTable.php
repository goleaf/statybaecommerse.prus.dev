<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogs\Tables;

use App\Models\ReferralCode;
use App\Models\User;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ReferralCodeUsageLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referralCode.code')
                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('admin.referral_code_usage_logs.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label(__('admin.referral_code_usage_logs.ip_address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referrer')
                    ->label(__('admin.referral_code_usage_logs.referrer'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state) || $state === '') {
                            return null;
                        }

                        return strlen((string) $state) > 30 ? $state : null;
                    }),
                TextColumn::make('user_agent')
                    ->label(__('admin.referral_code_usage_logs.user_agent'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state) || $state === '') {
                            return null;
                        }

                        return strlen((string) $state) > 50 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('referral_code_id')
                    ->label(__('admin.referral_code_usage_logs.referral_code'))
                    ->options(ReferralCode::pluck('code', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('admin.referral_code_usage_logs.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
