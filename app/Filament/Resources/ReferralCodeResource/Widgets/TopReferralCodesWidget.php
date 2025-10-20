<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Widgets;

use App\Models\ReferralCode;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

final class TopReferralCodesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('referral_codes.widgets.top_codes');
    }

    protected function getTableQuery(): Builder
    {
        return ReferralCode::query()
            ->withoutGlobalScopes()
            ->select(['id', 'code', 'title', 'usage_count', 'reward_amount', 'reward_type'])
            ->orderByDesc('usage_count')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('code')
                ->label(__('referral_codes.fields.code'))
                ->copyable()
                ->copyMessage(__('referral_codes.notifications.url_copied')),
            TextColumn::make('title')
                ->label(__('referral_codes.fields.title'))
                ->formatStateUsing(fn (?string $state): string => $state ?? __('referral_codes.no_title'))
                ->wrap(),
            TextColumn::make('usage_count')
                ->label(__('referral_codes.fields.usage_count'))
                ->sortable(),
            TextColumn::make('reward_amount')
                ->label(__('referral_codes.fields.reward_amount'))
                ->formatStateUsing(function (?string $state, ReferralCode $record): string {
                    if ($state === null) {
                        return __('referral_codes.no_reward');
                    }

                    $amount = Number::currency((float) $state, 'EUR');
                    $type = $record->reward_type;

                    return $type
                        ? sprintf('%s (%s)', $amount, __('referral_codes.reward_types.' . $type))
                        : $amount;
                }),
        ];
    }
}
