<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopSellingProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Products';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
                    ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_revenue')
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('orders', function($join) {
                        $join->on('order_items.order_id', '=', 'orders.id')
                             ->where('orders.status', '!=', 'cancelled');
                    })
                    ->groupBy('products.id')
                    ->orderByRaw('total_sold DESC')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('media')
                    ->label('')
                    ->collection('products')
                    ->conversion('thumb')
                    ->size(40)
                    ->circular()
                    ->defaultImageUrl('/images/placeholder-product.jpg'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.product'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Product $record): ?string {
                        return $record->name;
                    }),
                
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.price'))
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('EUR')
                    ->color('primary')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.stock'))
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.view', $record))
                    ->icon('heroicon-m-eye')
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
