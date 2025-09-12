<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB as Database;

final class TopProductsWidget extends BaseWidget
{
    public function getHeading(): string
    {
        return __('admin.widgets.top_products');
    }

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectSub(
                        AnalyticsEvent::selectRaw('COUNT(*)')
                            ->where('event_type', 'product_view')
                            ->whereRaw('JSON_EXTRACT(properties, "$.product_id") = products.id')
                            ->where('created_at', '>=', now()->subDays(7)),
                        'views_count'
                    )
                    ->selectSub(
                        AnalyticsEvent::selectRaw('COUNT(*)')
                            ->where('event_type', 'add_to_cart')
                            ->whereRaw('JSON_EXTRACT(properties, "$.product_id") = products.id')
                            ->where('created_at', '>=', now()->subDays(7)),
                        'cart_adds_count'
                    )
                    ->selectSub(
                        Database::table('order_items')
                            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                            ->whereColumn('order_items.product_id', 'products.id')
                            ->whereExists(function ($query) {
                                $query
                                    ->select(Database::raw(1))
                                    ->from('orders')
                                    ->whereColumn('orders.id', 'order_items.order_id')
                                    ->where('orders.status', 'completed')
                                    ->whereNull('orders.deleted_at');
                            }),
                        'total_sold'
                    )
                    ->where('products.is_visible', true)
                    ->orderByRaw('COALESCE(views_count, 0) + COALESCE(total_sold, 0) DESC')
            )
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->conversion('image-sm')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('views_count')
                    ->label(__('admin.widgets.views'))
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('cart_adds_count')
                    ->label(__('admin.widgets.cart_adds'))
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label(__('admin.widgets.total_sold'))
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->default(0),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.widgets.stock'))
                    ->badge()
                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->alignCenter(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn(Product $record): string => route('product.show', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-eye'),
                Action::make('edit')
                    ->label(__('admin.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn(Product $record): string => route('filament.admin.resources.products.edit', $record)),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
