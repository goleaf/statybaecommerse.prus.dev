<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

final class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('admin.widgets.recent_orders');
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.table.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.table.customer'))
                    ->searchable()
                    ->sortable()
                    ->default('Guest'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.table.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('admin.table.payment'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.table.total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->label(__('admin.actions.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->openUrlInNewTab(),
                Actions\Action::make('process')
                    ->label(__('admin.actions.process'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('info')
                    ->visible(fn(Order $record): bool => $record->status === 'pending')
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'processing']);
                        $this->dispatch('order-processed');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}

