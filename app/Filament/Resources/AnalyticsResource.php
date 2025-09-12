<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
final class AnalyticsResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';


    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.analytics');
    }

    public static function getModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function getPluralModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items.product'])
                    ->select([
                        'orders.*',
                        DB::raw(DatabaseDateService::dateExpression('orders.created_at') . ' as order_date'),
                        DB::raw(DatabaseDateService::monthExpression('orders.created_at') . ' as order_month'),
                        DB::raw(DatabaseDateService::yearExpression('orders.created_at') . ' as order_year'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_date')
                    ->label(__('analytics.date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label(__('analytics.order_number'))
                    ->searchable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('analytics.customer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label(__('analytics.items'))
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('analytics.total'))
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label(__('analytics.total_revenue')),
                        Tables\Columns\Summarizers\Average::make()
                            ->money('EUR')
                            ->label(__('analytics.avg_order_value')),
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('analytics.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('analytics.created'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('analytics.from_date')),
                        DatePicker::make('created_until')
                            ->label(__('analytics.until_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label(__('analytics.order_date_range')),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('analytics.status'))
                    ->options([
                        'pending' => __('analytics.pending'),
                        'processing' => __('analytics.processing'),
                        'shipped' => __('analytics.shipped'),
                        'delivered' => __('analytics.delivered'),
                        'cancelled' => __('analytics.cancelled'),
                        'refunded' => __('analytics.refunded'),
                    ]),
                Tables\Filters\Filter::make('high_value')
                    ->label(__('analytics.high_value_orders'))
                    ->query(fn(Builder $query): Builder => $query->where('total', '>', 500))
                    ->toggle(),
                Tables\Filters\Filter::make('this_month')
                    ->label(__('analytics.this_month'))
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('analytics.export_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('order_month')
                    ->label(__('analytics.month'))
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('status')
                    ->label(__('analytics.status'))
                    ->collapsible(),
            ])
            ->poll('30s')
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\AnalyticsDashboard::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_analytics') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingOrders = Order::where('status', 'pending')->count();
        return $pendingOrders > 0 ? (string) $pendingOrders : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
