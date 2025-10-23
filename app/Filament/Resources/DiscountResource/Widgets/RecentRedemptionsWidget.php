<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Widgets;

use App\Models\DiscountRedemption;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class RecentRedemptionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Redemptions';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->query(
                DiscountRedemption::query()
                    ->with(['user', 'discount', 'code'])
                    ->latest('redeemed_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code.code')
                    ->label('Code')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label('Amount Saved')
                    ->sortable()
                    ->formatStateUsing(fn ($state, DiscountRedemption $record): string => $state === null
                        ? '-' : number_format((float) $state, 2).' '.($record->currency_code ?? 'EUR'))
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'redeemed',
                        'secondary' => 'refunded',
                        'danger' => 'cancelled',
                        'gray' => 'expired',
                    ]),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('redeemed_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
