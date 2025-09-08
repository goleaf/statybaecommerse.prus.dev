<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('admin.widgets.recent_orders');
    }

    public function getDescription(): ?string
    {
        return __('admin.widgets.recent_orders_description');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label(__('admin.fields.order_reference'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.fields.customer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.fields.total'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'gray' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->label(__('admin.actions.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
