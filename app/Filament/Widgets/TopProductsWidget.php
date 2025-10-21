<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

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
        return __('analytics.top_products');
    }

    public function table(Table $table): Table
    {
        $since = Carbon::now()->subDays(7);

        $query = Product::query()
            ->withoutGlobalScopes()
            ->select(['products.*'])
            ->selectRaw(
                '(SELECT COUNT(*) FROM analytics_events '
                    .'WHERE event_type = ? AND analytics_events.created_at >= ? '
                    ."AND JSON_EXTRACT(properties, '\$.product_id') = products.id) AS views_count",
                ['product_view', $since]
            )
            ->selectRaw(
                '(SELECT COUNT(*) FROM analytics_events '
                    .'WHERE event_type = ? AND analytics_events.created_at >= ? '
                    ."AND JSON_EXTRACT(properties, '\$.product_id') = products.id) AS cart_adds_count",
                ['add_to_cart', $since]
            )
            ->selectRaw(
                '(SELECT COALESCE(SUM(quantity), 0) FROM order_items '
                    .'JOIN orders ON orders.id = order_items.order_id '
                    .'WHERE order_items.product_id = products.id AND orders.status = ?) AS total_sold',
                ['completed']
            )
            ->where('products.is_visible', true)
            ->orderByDesc('total_sold');

        return $table
            ->query(fn (): Builder => $query)
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label(__('analytics.thumbnail'))
                    ->defaultImageUrl(fn (Product $product): ?string => $product->getFirstMediaUrl('default', 'thumbnail') ?: null)
                    ->square()
                    ->visibleOn(['md', 'lg']),
                TextColumn::make('name')
                    ->label(__('analytics.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('analytics.sku'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('analytics.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('views_count')
                    ->label(__('analytics.views'))
                    ->sortable(),
                TextColumn::make('cart_adds_count')
                    ->label(__('analytics.cart_adds'))
                    ->sortable(),
                TextColumn::make('total_sold')
                    ->label(__('analytics.total_sold'))
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
