<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class ModernSalesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('analytics.recent_sales');
    }

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items.product.brand'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('Order'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-o-document-text')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->url(fn(Order $record): ?string =>
                        $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'primary' => 'processing',
                        'info' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'confirmed',
                        'heroicon-o-cog-6-tooth' => 'processing',
                        'heroicon-o-truck' => 'shipped',
                        'heroicon-o-check-badge' => 'delivered',
                        'heroicon-o-x-circle' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->icon('heroicon-o-currency-euro'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-cube'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn(Order $record): string =>
                        route('filament.admin.resources.orders.view', $record))
                    ->openUrlInNewTab(),
                Action::make('edit_status')
                    ->label(__('Update Status'))
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => __('Pending'),
                                'confirmed' => __('Confirmed'),
                                'processing' => __('Processing'),
                                'shipped' => __('Shipped'),
                                'delivered' => __('Delivered'),
                                'cancelled' => __('Cancelled'),
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, Order $record): void {
                        $record->update($data);
                    }),
            ])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading(__('No orders yet'))
            ->emptyStateDescription(__('Orders will appear here once customers start purchasing.'))
            ->striped()
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
