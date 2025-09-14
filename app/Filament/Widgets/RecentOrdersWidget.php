<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * RecentOrdersWidget
 * 
 * Filament v4 widget for RecentOrdersWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 */
class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'orders.widgets.recent_orders';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Order::query()->latest()->limit(10))->columns([TextColumn::make('number')->label('orders.number')->searchable()->sortable()->weight(FontWeight::Bold)->copyable(), TextColumn::make('user.name')->label('orders.customer')->searchable()->sortable()->limit(30), BadgeColumn::make('status')->label('orders.status')->colors(['warning' => 'pending', 'info' => 'processing', 'success' => ['confirmed', 'shipped', 'delivered', 'completed'], 'danger' => 'cancelled'])->formatStateUsing(fn(string $state): string => __("orders.statuses.{$state}")), TextColumn::make('total')->label('orders.total')->money('EUR')->sortable()->weight(FontWeight::Bold), TextColumn::make('created_at')->label('orders.created_at')->dateTime()->sortable()])->actions([ActionGroup::make([Action::make('view')->label('orders.actions.view')->icon('heroicon-o-eye')->url(fn(Order $record): string => route('filament.admin.resources.orders.view', $record))->openUrlInNewTab(), Action::make('edit')->label('orders.actions.edit')->icon('heroicon-o-pencil')->url(fn(Order $record): string => route('filament.admin.resources.orders.edit', $record))->openUrlInNewTab()])])->paginated(false);
    }
}