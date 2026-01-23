<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\DiscountRedemption;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDiscountRedemptionsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function getHeading(): string
    {
        return __('admin.widgets.recent_redemptions');
    }

    public function table(Table $table): Table
    {
        // Configure the widget table to meet the Filament v4 return type contract.
        return $table
            ->query(
                DiscountRedemption::query()
                    ->with(['discount', 'code', 'user', 'order'])
                    ->latest('redeemed_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('discount.name')
                    ->label(__('discount_redemptions.fields.discount'))
                    ->weight(FontWeight::Bold)
                    ->searchable(),
                Tables\Columns\TextColumn::make('code.code')
                    ->label(__('discount_redemptions.fields.code'))
                    ->badge()
                    ->color('info')
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('discount_redemptions.fields.user'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label(__('discount_redemptions.fields.amount_saved'))
                    ->money('EUR')
                    ->weight(FontWeight::Bold)
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning'   => 'pending',
                        'success'   => 'redeemed',
                        'danger'    => 'expired',
                        'secondary' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label(__('discount_redemptions.fields.redeemed_at'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (DiscountRedemption $record): string => route('filament.admin.resources.discount-redemptions.view', $record)),
            ])
            ->defaultSort('redeemed_at', 'desc');
    }
}
