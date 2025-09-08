<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

final class RecentOrders extends BaseWidget
{
    public function getHeading(): string
    {
        return __('admin.widgets.recent_orders');
    }

    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.table.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.table.customer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.table.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.table.total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('admin.table.payment'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('admin.actions.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn(Order $record): string =>
                        route('filament.admin.resources.orders.view', ['record' => $record])),
            ])
            ->emptyStateHeading(__('admin.empty.no_orders'))
            ->emptyStateDescription(__('admin.empty.orders_will_appear_here'))
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.recent_orders_description');
    }
}
