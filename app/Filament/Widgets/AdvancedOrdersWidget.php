<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class AdvancedOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    
    protected static ?string $heading = 'Recent Orders';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'items.product'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('Order #'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->url(fn (Order $record): string => route('filament.admin.resources.users.view', $record->user))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'warning',
                        'processing' => 'primary',
                        'shipped' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('invoice')
                    ->label(__('Invoice'))
                    ->icon('heroicon-m-document-text')
                    ->action(function (Order $record) {
                        // Generate invoice PDF
                        return response()->download(
                            storage_path("app/invoices/invoice-{$record->number}.pdf")
                        );
                    })
                    ->visible(fn (Order $record): bool => in_array($record->status, ['confirmed', 'processing', 'shipped', 'delivered'])),
            ])
            ->emptyStateHeading(__('No Recent Orders'))
            ->emptyStateDescription(__('Orders will appear here once customers start placing them.'))
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->poll('30s');
    }
    
    public static function canView(): bool
    {
        return auth()->user()->can('view_order');
    }
}
