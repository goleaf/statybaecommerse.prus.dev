<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Tables;

use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use function strlen;

final class RecommendationAnalyticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('block.name')
                    ->label(__('recommendation_analytics.block'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('config.name')
                    ->label(__('recommendation_analytics.config'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('recommendation_analytics.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('recommendation_analytics.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('action')
                    ->label(__('recommendation_analytics.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'view'        => 'info',
                        'click'       => 'success',
                        'add_to_cart' => 'warning',
                        'purchase'    => 'danger',
                        default       => 'gray',
                    }),
                TextColumn::make('ctr')
                    ->label(__('recommendation_analytics.ctr'))
                    ->numeric(decimalPlaces: 4)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label(__('recommendation_analytics.conversion_rate'))
                    ->numeric(decimalPlaces: 4)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('recommendation_analytics.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('block_id')
                    ->label(__('recommendation_analytics.block'))
                    ->options(fn (): array => RecommendationBlock::pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('config_id')
                    ->label(__('recommendation_analytics.config'))
                    ->options(fn (): array => RecommendationConfig::pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('recommendation_analytics.user'))
                    ->options(fn (): array => User::pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label(__('recommendation_analytics.product'))
                    ->options(fn (): array => Product::withoutGlobalScopes()->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('action')
                    ->label(__('recommendation_analytics.action'))
                    ->options([
                        'view'        => __('recommendation_analytics.actions.view'),
                        'click'       => __('recommendation_analytics.actions.click'),
                        'add_to_cart' => __('recommendation_analytics.actions.add_to_cart'),
                        'purchase'    => __('recommendation_analytics.actions.purchase'),
                    ]),
                Filter::make('date')
                    ->label(__('recommendation_analytics.date'))
                    ->form([
                        SupportFlatpickr::makeDate('value')->label(__('recommendation_analytics.date')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $date): Builder => $q->whereDate('date', '=', $date),
                    )),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
