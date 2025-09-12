<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return __('analytics.top_selling_products');
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereHas('orderItems.order', function (Builder $query) {
                        $query->where('status', 'completed');
                    })
                    ->withCount(['orderItems' => function (Builder $query) {
                        $query->whereHas('order', function (Builder $query) {
                            $query->where('status', 'completed');
                        });
                    }])
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'completed')
                    ->selectRaw('products.*, SUM(order_items.quantity) as order_items_sum_quantity')
                    ->groupBy('products.id')
                    ->orderByDesc('order_items_sum_quantity')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('media')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.table.sku'))
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.table.price'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_items_sum_quantity')
                    ->label(__('admin.table.total_sold'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('order_items_count')
                    ->label(__('admin.table.orders'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.table.stock'))
                    ->numeric()
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state > 50 => 'success',
                        $state > 10 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('analytics.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn(Product $record): string => route('filament.admin.resources.products.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}

