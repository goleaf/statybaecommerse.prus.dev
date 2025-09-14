<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Widgets;

use App\Models\Referral;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final /**
 * RecentReferralsWidget
 * 
 * Filament resource for admin panel management.
 */
class RecentReferralsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function getHeading(): string
    {
        return __('referrals.recent_referrals');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Referral::query()
                    ->with(['referrer', 'referred', 'rewards'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('referral_code')
                    ->label(__('referrals.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->copyable(),

                Tables\Columns\TextColumn::make('referrer.name')
                    ->label(__('referrals.referrer'))
                    ->searchable()
                    ->sortable()
                    ->url(fn (Referral $record): string => route('filament.admin.resources.users.view', $record->referrer_id)),

                Tables\Columns\TextColumn::make('referred.name')
                    ->label(__('referrals.referred_user'))
                    ->searchable()
                    ->sortable()
                    ->url(fn (Referral $record): string => route('filament.admin.resources.users.view', $record->referred_id)),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('referrals.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'expired' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('rewards_count')
                    ->label(__('referrals.rewards'))
                    ->counts('rewards')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_rewards_amount')
                    ->label(__('referrals.total_rewards_amount'))
                    ->getStateUsing(fn (Referral $record): string => '€'.number_format($record->rewards()->sum('amount'), 2)
                    )
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('referrals.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('referrals.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('referrals.never_expires'))
                    ->since()
                    ->color(fn (?string $state): string => match (true) {
                        $state === null => 'gray',
                        now()->parse($state)->isPast() => 'danger',
                        now()->parse($state)->isBefore(now()->addDays(7)) => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('referrals.status'))
                    ->options([
                        'pending' => __('referrals.status_pending'),
                        'completed' => __('referrals.status_completed'),
                        'expired' => __('referrals.status_expired'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Referral $record): string => route('filament.admin.resources.referrals.view', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('30s');
    }
}
