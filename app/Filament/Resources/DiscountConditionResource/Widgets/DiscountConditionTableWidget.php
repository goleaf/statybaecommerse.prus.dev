<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class DiscountConditionTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Discount Conditions';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DiscountCondition::query()
                    ->with('discount')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('discount.name')
                    ->label(__('discount_conditions.fields.discount'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('discount_conditions.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product', 'category', 'brand', 'collection' => 'info',
                        'cart_total', 'item_qty' => 'warning',
                        'zone', 'channel', 'currency' => 'success',
                        'customer_group', 'user', 'partner_tier' => 'primary',
                        'first_order', 'day_time' => 'secondary',
                        'custom_script' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => DiscountCondition::getTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('operator')
                    ->label(__('discount_conditions.fields.operator'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => DiscountCondition::getOperators()[$state] ?? $state),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('discount_conditions.fields.value'))
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state)) {
                            return implode(', ', $state);
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('discount_conditions.fields.priority'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        $state <= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discount_conditions.fields.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('discount_conditions.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('discount_conditions.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (DiscountCondition $record): string => route('filament.admin.resources.discount-conditions.view', $record)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
