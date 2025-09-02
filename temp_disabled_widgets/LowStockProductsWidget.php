<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class LowStockProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Products';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['brand:id,name'])
                    ->where('manage_stock', true)
                    ->whereRaw('stock_quantity <= low_stock_threshold')
                    ->where('is_visible', true)
                    ->orderBy('stock_quantity')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('translations.image'))
                    ->getStateUsing(fn (Product $record): ?string => $record->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.product_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('translations.sku'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('translations.brand'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('translations.current_stock'))
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label(__('translations.threshold'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status_badge')
                    ->label(__('translations.status'))
                    ->getStateUsing(fn (Product $record): string => match (true) {
                        $record->stock_quantity <= 0 => __('translations.out_of_stock'),
                        $record->stock_quantity <= $record->low_stock_threshold => __('translations.low_stock'),
                        default => __('translations.in_stock'),
                    })
                    ->colors([
                        'danger' => fn (string $state): bool => $state === __('translations.out_of_stock'),
                        'warning' => fn (string $state): bool => $state === __('translations.low_stock'),
                        'success' => fn (string $state): bool => $state === __('translations.in_stock'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\Action::make('restock')
                    ->icon('heroicon-m-arrow-up')
                    ->color('success')
                    ->form([
                        Tables\Actions\Components\TextInput::make('quantity')
                            ->label(__('translations.add_stock'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->update([
                            'stock_quantity' => $record->stock_quantity + $data['quantity']
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('translations.stock_updated'))
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading(__('translations.no_low_stock_products'))
            ->emptyStateDescription(__('translations.all_products_well_stocked'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
