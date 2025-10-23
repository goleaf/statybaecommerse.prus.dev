<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summaries\Average;
use Filament\Tables\Columns\Summaries\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as TranslatableResource;
use UnitEnum;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

final class AnalyticsResource extends Resource
{
    use TranslatableResource;

    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * Preserve the typed navigation group union to keep enum-backed grouping working across PHP upgrades.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Analytics;

    public static function getNavigationLabel(): string
    {
        return __('analytics.analytics_dashboard');
    }

    public static function getModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function getPluralModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) Order::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        // Keep the dashboard read-only by returning the base form configuration untouched.
        return $form;
    }

    public static function table(Table $table): Table|array
    {
        $currency = config('app.currency', 'EUR');

        return $table
            ->query(fn (Builder $query): Builder => $query->with(['user'])->withCount('items'))
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('order_date')
                    ->label(__('analytics.date'))
                    ->state(fn (Order $record) => $record->created_at)
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('number')
                    ->label(__('analytics.order_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label(__('analytics.customer'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('items_count')
                    ->label(__('analytics.items'))
                    ->counts('items')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label(__('analytics.total'))
                    ->money($currency)
                    ->sortable()
                    ->summarize([
                        Sum::make()->label(__('analytics.total_revenue')),
                        Average::make()->label(__('analytics.avg_order_value')),
                    ])
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('analytics.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'info',
                        'completed', 'delivered' => 'success',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('analytics.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('analytics.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('analytics.status'))
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'completed'  => 'Completed',
                        'cancelled'  => 'Cancelled',
                    ]),
                Filter::make('created_at')
                    ->label(__('analytics.order_date_range'))
                    ->form([
                        Flatpickr::makeRange('range')
                            ->label(__('analytics.from_date'))

                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
                Filter::make('high_value')
                    ->label(__('analytics.high_value_orders'))
                    ->query(fn (Builder $query): Builder => $query->where('total', '>=', 500))
                    ->indicateUsing(fn () => __('analytics.high_value_orders')),
                Filter::make('this_month')
                    ->label(__('analytics.this_month'))
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
                    ->indicateUsing(fn () => __('analytics.this_month')),
            ])
            ->groups([
                Group::make('status')
                    ->label(__('analytics.status')),
                Group::make('created_at')
                    ->label(__('analytics.month'))
                    ->date()
                    ->collapsible(),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('analytics.view'))
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\AnalyticsDashboard::route('/'),
        ];
    }
}
