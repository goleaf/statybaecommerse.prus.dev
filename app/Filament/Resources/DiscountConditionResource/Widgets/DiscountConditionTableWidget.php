<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class DiscountConditionTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->query(
                DiscountCondition::query()->latest('created_at')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('translated_name')
                    ->label(__('discount_conditions.name'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->formatStateUsing(fn (string $state): string => __('discount_conditions.types.' . Str::slug($state, '_')))
                    ->badge()
                    ->color(fn (string $state): string => $this->typeColor($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->formatStateUsing(fn (string $state): string => __('discount_conditions.operators.' . Str::slug($state, '_')))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'minimum_amount'   => 'blue',
                        'minimum_quantity' => 'green',
                        'customer_group'   => 'purple',
                        'product_category' => 'orange',
                        'date_range'       => 'red',
                        default            => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->formatStateUsing(fn (?string $state, DiscountCondition $record): string => match ($record->type) {
                        'minimum_amount'   => '€' . number_format((float) $state, 2),
                        'minimum_quantity' => (string) $state,
                        default            => $state ?? '-',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('discount_conditions.priority'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('discount_conditions.position'))
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
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_array($value)) {
            return implode(', ', Arr::flatten($value));
        }

        if (is_bool($value)) {
            return $value ? __('discount_conditions.boolean_yes') : __('discount_conditions.boolean_no');
        }

        return (string) $value;
    }

    private function typeColor(string $type): string
    {
        return match ($type) {
            'cart_total' => 'primary',
            'item_qty'   => 'success',
            'product', 'attribute_value' => 'info',
            'category', 'collection' => 'warning',
            'brand' => 'purple',
            'channel', 'currency' => 'indigo',
            'customer_group', 'partner_tier' => 'cyan',
            'user'          => 'teal',
            'first_order'   => 'emerald',
            'day_time'      => 'amber',
            'custom_script' => 'pink',
            default         => 'gray',
        };
    }
}
