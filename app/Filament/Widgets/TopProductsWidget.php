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

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Products';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

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
                    ->where('status', 'active')
                    ->orderByDesc('views_count')
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
                    ->label('Views')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('cart_adds_count')
                    ->label('Cart Adds')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->alignCenter(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn(Product $record): string => route('products.show', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
