<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Support\Facades\DB as Database;

final class LowStockAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('admin.widgets.low_stock_alerts');
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_visible', true)
                    ->where('manage_stock', true)
                    ->where('stock_quantity', '<=', Database::raw('low_stock_threshold'))
                    ->with(['brand', 'categories'])
                    ->orderBy('stock_quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Product'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('Stock'))
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->icon(fn(int $state): string => match (true) {
                        $state <= 0 => 'heroicon-m-x-circle',
                        $state <= 5 => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-check-circle',
                    }),
                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label(__('Threshold'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label(__('Categories'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('restock')
                    ->label(__('Restock'))
                    ->icon('heroicon-m-plus')
                    ->color('success')
                    ->form([
                        \Filament\Actions\Modal\Components\TextInput::make('quantity')
                            ->label(__('Add Quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->increment('stock_quantity', $data['quantity']);

                        activity()
                            ->performedOn($record)
                            ->withProperties([
                                'added_quantity' => $data['quantity'],
                                'new_stock' => $record->fresh()->stock_quantity,
                            ])
                            ->log("Stock restocked with {$data['quantity']} units");
                    })
                    ->successNotificationTitle(__('Stock updated successfully')),
                Action::make('edit')
                    ->label(__('Edit Product'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn(Product $record): string => route('filament.admin.resources.products.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading(__('No Low Stock Items'))
            ->emptyStateDescription(__('All products are well stocked!'))
            ->emptyStateIcon('heroicon-o-check-circle')
            ->poll('60s');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('view_product');
    }
}
