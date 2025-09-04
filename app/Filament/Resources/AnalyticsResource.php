<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

final class AnalyticsResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Analytics Dashboard');
    }

    public static function getModelLabel(): string
    {
        return __('Analytics');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Analytics');
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
                    ->label(__('Date'))
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label(__('Order #'))
                    ->searchable()
                    ->copyable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label(__('Total Revenue')),
                        Tables\Columns\Summarizers\Average::make()
                            ->money('EUR')
                            ->label(__('Avg Order Value')),
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
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
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\DateFilter::make('created_at')
                    ->label(__('Order Date')),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'processing' => __('Processing'),
                        'shipped' => __('Shipped'),
                        'delivered' => __('Delivered'),
                        'cancelled' => __('Cancelled'),
                        'refunded' => __('Refunded'),
                    ]),
                Tables\Filters\Filter::make('high_value')
                    ->label(__('High Value Orders'))
                    ->query(fn(Builder $query): Builder => $query->where('total', '>', 500))
                    ->toggle(),
                Tables\Filters\Filter::make('this_month')
                    ->label(__('This Month'))
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Order $record): string => route('admin.orders.show', $record)),
            ])
            ->bulkActions([
                Tables\Actions\ExportBulkAction::make()
                    ->label(__('Export Selected')),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('order_month')
                    ->label(__('Month'))
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('status')
                    ->label(__('Status'))
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
