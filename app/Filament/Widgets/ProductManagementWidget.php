<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class ProductManagementWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    
    protected static ?string $heading = 'Product Management';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['brand', 'category', 'prices'])
                    ->where('is_visible', true)
                    ->latest()
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('Image'))
                    ->getStateUsing(fn (Product $record): ?string => 
                        $record->getFirstMediaUrl('gallery') ?: null
                    )
                    ->size(60)
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Product Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-cube')
                    ->color('primary')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->icon('heroicon-o-building-storefront'),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-tag'),
                    
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->searchable()
                    ->copyable()
                    ->tooltip(__('Click to copy'))
                    ->icon('heroicon-o-qr-code'),
                    
                Tables\Columns\TextColumn::make('stock_status')
                    ->badge()
                    ->label(__('Stock'))
                    ->getStateUsing(function (Product $record): string {
                        if (!$record->manage_stock) return 'unlimited';
                        if ($record->stock_quantity > $record->low_stock_threshold) return 'in_stock';
                        if ($record->stock_quantity > 0) return 'low_stock';
                        return 'out_of_stock';
                    })
                    ->colors([
                        'success' => 'in_stock',
                        'success' => 'unlimited',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'in_stock',
                        'heroicon-o-infinity' => 'unlimited',
                        'heroicon-o-exclamation-triangle' => 'low_stock',
                        'heroicon-o-x-circle' => 'out_of_stock',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'in_stock' => __('In Stock'),
                        'unlimited' => __('Unlimited'),
                        'low_stock' => __('Low Stock'),
                        'out_of_stock' => __('Out of Stock'),
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Product $record): string => 
                        !$record->manage_stock ? 'gray' :
                        ($record->stock_quantity > $record->low_stock_threshold ? 'success' :
                        ($record->stock_quantity > 0 ? 'warning' : 'danger'))
                    ),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->getStateUsing(function (Product $record): ?string {
                        $price = $record->prices->first();
                        return $price ? "€" . number_format($price->amount, 2) : null;
                    })
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->icon('heroicon-o-currency-euro'),
                    
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label(__('Visible'))
                    ->onIcon('heroicon-o-eye')
                    ->offIcon('heroicon-o-eye-slash')
                    ->onColor('success')
                    ->offColor('danger'),
                    
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->onIcon('heroicon-o-star')
                    ->offIcon('heroicon-o-star')
                    ->onColor('warning')
                    ->offColor('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Product $record): string => 
                        route('filament.admin.resources.products.view', $record)
                    )
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->url(fn (Product $record): string => 
                        route('filament.admin.resources.products.edit', $record)
                    ),
                    
                Tables\Actions\Action::make('quick_stock')
                    ->label(__('Update Stock'))
                    ->icon('heroicon-o-cube')
                    ->color('primary')
                    ->form([
                        Tables\Columns\NumericColumn::make('stock_quantity')
                            ->label(__('Stock Quantity'))
                            ->required()
                            ->minValue(0),
                    ])
                    ->action(function (array $data, Product $record): void {
                        $record->update($data);
                    })
                    ->visible(fn (Product $record): bool => $record->manage_stock),
            ])
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateHeading(__('No products found'))
            ->emptyStateDescription(__('Create your first product to get started.'))
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
