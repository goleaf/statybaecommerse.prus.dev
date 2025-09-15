<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class DiscountConditionTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Recent Discount Conditions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DiscountCondition::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('discount_conditions.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->formatStateUsing(fn (string $state): string => __("discount_conditions.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'minimum_amount' => 'blue',
                        'minimum_quantity' => 'green',
                        'customer_group' => 'purple',
                        'product_category' => 'orange',
                        'date_range' => 'red',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->formatStateUsing(fn (?string $state, DiscountCondition $record): string => match ($record->type) {
                        'minimum_amount' => '€' . number_format((float) $state, 2),
                        'minimum_quantity' => (string) $state,
                        default => $state ?? '-',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('discount_conditions.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('discount_conditions.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('discount_conditions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
