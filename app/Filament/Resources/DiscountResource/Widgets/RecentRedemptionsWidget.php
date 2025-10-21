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
        return $table
            ->query(
                DiscountRedemption::query()
                    ->with(['user', 'discount', 'discountCode'])
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
                Tables\Columns\TextColumn::make('discount_code.code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('original_amount')
                    ->label('Original')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('EUR')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Final')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('redeemed_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
