<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole('admin'), 403);
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function getHeading(): ?string
    {
        return __('analytics.top_selling_products');
    }

    public function table(Table $table): Table
    {
        $query = Product::query()
            ->select(['products.*'])
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as order_items_sum_quantity')
            ->selectRaw('COUNT(order_items.id) as order_items_count')
            ->groupBy('products.id')
            ->orderByDesc('order_items_sum_quantity')
            ->limit(10);

        return $table
            ->query($query)
            ->columns([
                ImageColumn::make('media'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('price')->money('EUR')->sortable(),
                TextColumn::make('order_items_sum_quantity')->label(__('analytics.sold_quantity'))->sortable(),
                TextColumn::make('order_items_count')->label(__('analytics.orders_count'))->sortable(),
                TextColumn::make('stock_quantity')->sortable(),
            ]);
    }
}
