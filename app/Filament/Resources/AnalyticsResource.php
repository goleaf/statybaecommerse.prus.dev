<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;
final class AnalyticsResource extends Resource
{
    use SpatieTranslatableResource; // Align translation support with other resources.

    /**
     * Mirror the Filament base class union so icon definitions support both enum-backed and string identifiers.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * Keep the union aligned with the Filament base so enum-backed groups resolve correctly.
     */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::Analytics->value;

    public static function getNavigationLabel(): string
    {
        // Surface the dashboard-specific label to match the refreshed navigation copy.
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

    public static function form(Schema $schema): Schema   
    {
        return $schema;
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            // Preload frequently accessed relationships so table metrics do not suffer from N+1 queries.
            ->modifyQueryUsing(
                static fn (Builder $query): Builder => $query->with([
                    'user:id,name,email',
                    'items:id,order_id',
                    'channel:id,name',
                ])
            )
            ->columns([
                // Order number helps link analytics rows back to operational records.
                TextColumn::make('number')
                    ->label(__('analytics.order_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Count::make()->label(__('analytics.total_orders')),
                    ]),
                // Preserve the localized order date column but keep the explicit accessor for clarity.
                TextColumn::make('order_date')
                    ->label(__('analytics.columns.order_date'))
                    ->date()
                    ->sortable()
                    ->getStateUsing(static fn (Order $record) => $record->created_at)
                    ->toggleable(),
                // Show the purchasing customer's name for segmentation.
                TextColumn::make('user.name')
                    ->label(__('analytics.customer'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                // Provide the customer's email to support outreach workflows directly from analytics.
                TextColumn::make('user.email')
                    ->label(__('analytics.customer_email'))
                    ->searchable()
                    ->toggleable(),
                // Channel names aid in marketing attribution when reviewing performance.
                TextColumn::make('channel.name')
                    ->label(__('analytics.channel'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Count items through the eager-loaded relationship for accurate basket analysis.
                TextColumn::make('items_count')
                    ->label(__('analytics.items'))
                    ->getStateUsing(static fn (Order $record): int => $record->items->count())
                    ->toggleable(),
                // Total revenue column contributes to aggregate KPIs.
                TextColumn::make('total')
                    ->label(__('analytics.total'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make('total')->label(__('analytics.total_revenue'))->money('EUR'),
                        Average::make('total')->label(__('analytics.avg_order_value'))->money('EUR'),
                    ]),
                // Translate status badges so the dashboard remains localized.
                TextColumn::make('status')
                    ->label(__('analytics.status'))
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => __('analytics.' . strtolower($state)))
                    ->color(static fn (string $state): string => match ($state) {
                        'completed', 'delivered' => 'success',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                // Maintain timestamps for auditing and allow toggling visibility per user preference.
                TextColumn::make('created_at')
                    ->label(__('analytics.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('analytics.updated'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('analytics.status'))
                    ->options([
                        'pending'    => __('analytics.pending'),
                        'processing' => __('analytics.processing'),
                        'completed'  => __('analytics.completed'),
                        'cancelled'  => __('analytics.cancelled'),
                        'shipped'    => __('analytics.shipped'),
                        'delivered'  => __('analytics.delivered'),
                        'refunded'   => __('analytics.refunded'),
                    ])
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label(__('analytics.customer'))
                    ->searchable(),
                SelectFilter::make('channel_id')
                    ->relationship('channel', 'name')
                    ->label(__('analytics.channel'))
                    ->searchable(),
                Filter::make('created_at')
                    ->label(__('analytics.order_date_range'))
                    ->form([
                        SupportFlatpickr::makeRange('range', displayFormat: 'Y-m-d', format: 'Y-m-d'),
                    ])
                    ->indicateUsing(static fn (array $data): ?string => isset($data['range']['start'], $data['range']['end'])
                        ? __('analytics.order_date_range') . ': ' . $data['range']['start'] . ' → ' . $data['range']['end']
                        : null)
                    ->query(static fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
                Filter::make('high_value')
                    ->label(__('analytics.high_value_orders'))
                    ->query(static fn (Builder $query): Builder => $query->where('total', '>=', 500)),
                Filter::make('this_month')
                    ->label(__('analytics.this_month'))
                    ->query(static fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ])),
            ])
            ->groups([
                // Allow analysts to cluster orders by lifecycle stage.
                Group::make('status')
                    ->label(__('analytics.status'))
                    ->collapsible(),
                // Provide chronological grouping by month for period-over-period reviews.
                Group::make('created_at')
                    ->label(__('analytics.month'))
                    ->date('Y-m'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Deep-link into the order resource for detailed investigation of anomalies.
                ViewAction::make()
                    ->label(__('analytics.view_order'))
                    ->icon('heroicon-o-eye')
                    ->url(static fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
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
