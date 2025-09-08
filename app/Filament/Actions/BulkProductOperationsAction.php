<?php declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Collection;

final class BulkProductOperationsAction
{
    public static function make(): Action
    {
        return Action::make('bulkOperations')
            ->label(__('admin.actions.bulk_operations'))
            ->icon('heroicon-o-cog-6-tooth')
            ->color(Color::Blue)
            ->form([
                Forms\Components\Select::make('operation')
                    ->label(__('admin.fields.operation'))
                    ->options([
                        'update_prices' => __('admin.bulk_operations.update_prices'),
                        'update_stock' => __('admin.bulk_operations.update_stock'),
                        'toggle_visibility' => __('admin.bulk_operations.toggle_visibility'),
                        'assign_category' => __('admin.bulk_operations.assign_category'),
                        'update_brand' => __('admin.bulk_operations.update_brand'),
                        'apply_discount' => __('admin.bulk_operations.apply_discount'),
                        'export_data' => __('admin.bulk_operations.export_data'),
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('price_multiplier')
                        ->label(__('admin.fields.price_multiplier'))
                        ->numeric()
                        ->step(0.01)
                        ->default(1.0)
                        ->helperText(__('admin.help.price_multiplier')),
                        
                    Forms\Components\TextInput::make('price_addition')
                        ->label(__('admin.fields.price_addition'))
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->helperText(__('admin.help.price_addition')),
                ])
                ->visible(fn (Forms\Get $get): bool => $get('operation') === 'update_prices')
                ->columns(2),

                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('stock_quantity')
                        ->label(__('admin.fields.stock_quantity'))
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                        
                    Forms\Components\Select::make('stock_operation')
                        ->label(__('admin.fields.stock_operation'))
                        ->options([
                            'set' => __('admin.stock_operations.set'),
                            'add' => __('admin.stock_operations.add'),
                            'subtract' => __('admin.stock_operations.subtract'),
                        ])
                        ->default('set')
                        ->required(),
                ])
                ->visible(fn (Forms\Get $get): bool => $get('operation') === 'update_stock')
                ->columns(2),

                Forms\Components\Toggle::make('is_visible')
                    ->label(__('admin.fields.is_visible'))
                    ->default(true)
                    ->visible(fn (Forms\Get $get): bool => $get('operation') === 'toggle_visibility'),

                Forms\Components\Select::make('category_id')
                    ->label(__('admin.fields.category'))
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get): bool => $get('operation') === 'assign_category'),

                Forms\Components\Select::make('brand_id')
                    ->label(__('admin.fields.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get): bool => $get('operation') === 'update_brand'),

                Forms\Components\TextInput::make('discount_percentage')
                    ->label(__('admin.fields.discount_percentage'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required()
                    ->visible(fn (Forms\Get $get): bool => $get('operation') === 'apply_discount'),
            ])
            ->action(function (array $data, Collection $records): void {
                $operation = $data['operation'];
                $count = $records->count();

                match ($operation) {
                    'update_prices' => self::updatePrices($records, $data),
                    'update_stock' => self::updateStock($records, $data),
                    'toggle_visibility' => self::toggleVisibility($records, $data),
                    'assign_category' => self::assignCategory($records, $data),
                    'update_brand' => self::updateBrand($records, $data),
                    'apply_discount' => self::applyDiscount($records, $data),
                    'export_data' => self::exportData($records),
                };

                Notification::make()
                    ->title(__('admin.notifications.bulk_operation_completed'))
                    ->body(__('admin.notifications.processed_items', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    private static function updatePrices(Collection $products, array $data): void
    {
        $multiplier = $data['price_multiplier'] ?? 1.0;
        $addition = $data['price_addition'] ?? 0;

        $products->each(function (Product $product) use ($multiplier, $addition) {
            $newPrice = ($product->price * $multiplier) + $addition;
            $product->update(['price' => round($newPrice, 2)]);
        });
    }

    private static function updateStock(Collection $products, array $data): void
    {
        $quantity = $data['stock_quantity'];
        $operation = $data['stock_operation'];

        $products->each(function (Product $product) use ($quantity, $operation) {
            $newStock = match ($operation) {
                'set' => $quantity,
                'add' => $product->stock_quantity + $quantity,
                'subtract' => max(0, $product->stock_quantity - $quantity),
                default => $quantity,
            };
            
            $product->update(['stock_quantity' => $newStock]);
        });
    }

    private static function toggleVisibility(Collection $products, array $data): void
    {
        $isVisible = $data['is_visible'];
        $products->each(fn (Product $product) => $product->update(['is_visible' => $isVisible]));
    }

    private static function assignCategory(Collection $products, array $data): void
    {
        $categoryId = $data['category_id'];
        $products->each(function (Product $product) use ($categoryId) {
            if (!$product->categories()->where('category_id', $categoryId)->exists()) {
                $product->categories()->attach($categoryId);
            }
        });
    }

    private static function updateBrand(Collection $products, array $data): void
    {
        $brandId = $data['brand_id'];
        $products->each(fn (Product $product) => $product->update(['brand_id' => $brandId]));
    }

    private static function applyDiscount(Collection $products, array $data): void
    {
        $discountPercentage = $data['discount_percentage'];
        
        $products->each(function (Product $product) use ($discountPercentage) {
            $discountAmount = $product->price * ($discountPercentage / 100);
            $salePrice = $product->price - $discountAmount;
            $product->update(['sale_price' => round($salePrice, 2)]);
        });
    }

    private static function exportData(Collection $products): void
    {
        // Export functionality would be implemented here
        // For now, just a placeholder
    }
}


