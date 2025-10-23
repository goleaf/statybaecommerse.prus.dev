<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsResource\Pages;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class AnalyticsResource extends Resource
{
    use TranslatableResource;

    protected static ?string $model = Order::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = NavigationGroup::Analytics;

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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form;
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            // Avoid N+1 issues when rendering relationship data inside the table.
            ->modifyQueryUsing(static function (Builder $query): Builder {
                return $query
                    ->with([
                        'user:id,name,email',
                        'channel:id,name',
                    ])
                    ->withCount('items');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_date')
                    ->label(__('analytics.columns.order_date'))
                    ->date()
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(static fn (Order $record) => $record->created_at)
                    ->toggleable()
                    ->summarize(Count::make()
                        ->label(__('analytics.summary.orders_count'))),
                TextColumn::make('number')
                    ->label(__('analytics.columns.order_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('analytics.columns.customer_name'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.email')
                    ->label(__('analytics.columns.customer_email'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label(__('analytics.columns.items_count'))
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(static fn (Order $record): string => (string) ($record->items_count ?? 0)),
                TextColumn::make('total')
                    ->label(__('analytics.columns.order_total'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()->label(__('analytics.summary.total_revenue'))->money('EUR'),
                        Average::make()->label(__('analytics.summary.average_order_value'))->money('EUR'),
                    ]),
                TextColumn::make('status')
                    ->label(__('analytics.columns.status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(static fn (?string $state): ?string => $state === null ? null : __('analytics.'.strtolower($state)))
                    ->color(static function (?string $state): string {
                        // Provide quick-glance styling that matches our status palette.
                        return match ($state) {
                            'completed', 'delivered' => 'success',
                            'pending', 'processing' => 'warning',
                            'cancelled', 'refunded' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->toggleable(),
                TextColumn::make('channel.name')
                    ->label(__('analytics.columns.sales_channel'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('analytics.columns.placed_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('analytics.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('analytics.filters.status'))
                    ->placeholder(__('analytics.filters.status_placeholder'))
                    ->options([
                        'pending' => __('analytics.pending'),
                        'processing' => __('analytics.processing'),
                        'completed' => __('analytics.completed'),
                        'cancelled' => __('analytics.cancelled'),
                        'shipped' => __('analytics.shipped'),
                        'delivered' => __('analytics.delivered'),
                        'refunded' => __('analytics.refunded'),
                    ]),
                Filter::make('created_at')
                    ->label(__('analytics.filters.date_range'))
                    ->form([
                        Flatpickr::makeRange('range')
                            ->label(__('analytics.from_date'))
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(static fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
                Filter::make('high_value')
                    ->label(__('analytics.filters.high_value'))
                    ->query(static fn (Builder $query): Builder => $query->where('total', '>=', 500)),
                Filter::make('this_month')
                    ->label(__('analytics.filters.this_month'))
                    ->query(static fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])),
            ])
            ->groups([
                Group::make('status')
                    ->label(__('analytics.groups.status'))
                    ->collapsible(),
                Group::make('created_at')
                    ->label(__('analytics.groups.placed_at'))
                    ->date()
                    ->collapsible(),
            ])
            ->defaultGroup('status')
            ->actions([
                ViewAction::make()
                    ->label(__('analytics.actions.view_order'))
                    ->icon('heroicon-o-eye')
                    ->url(static fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->tooltip(__('analytics.actions.view_order')),
            ]);
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
