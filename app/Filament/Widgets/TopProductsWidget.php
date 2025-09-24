<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

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
        return __('admin.widgets.top_products');
    }

    public function table(Table $table): Table
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $query = Product::query()
            ->where('is_visible', true)
            ->addSelect(['views_count' => AnalyticsEvent::query()
                ->selectRaw('COUNT(*)')
                ->where('event_type', 'product_view')
                ->where('created_at', '>=', $sevenDaysAgo)
                ->whereColumn('properties->product_id', 'products.id'),
            ])
            ->addSelect(['cart_adds_count' => AnalyticsEvent::query()
                ->selectRaw('COUNT(*)')
                ->where('event_type', 'add_to_cart')
                ->where('created_at', '>=', $sevenDaysAgo)
                ->whereColumn('properties->product_id', 'products.id'),
            ])
            ->addSelect(['total_sold' => \App\Models\OrderItem::query()
                ->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('product_id', 'products.id')
                ->whereHas('order', function ($q): void {
                    $q->where('status', 'completed');
                }),
            ])
            ->orderByDesc('total_sold')
            ->limit(10);

        return $table
            ->query($query)
            ->columns([
                ImageColumn::make('images'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('price')->money('EUR')->sortable(),
                TextColumn::make('views_count'),
                TextColumn::make('cart_adds_count'),
                TextColumn::make('total_sold'),
            ])
            ->paginated();
    }
}
