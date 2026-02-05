<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Tables;

use App\Models\Product;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['brand', 'primaryImage']))
            ->columns([
                ImageColumn::make('primaryImage.path')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label(__('messages.brand'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('sales_sparkline')
                    ->label(__('admin.products.sales'))
                    ->formatStateUsing(static fn (): string => '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label(__('admin.products.cost_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weight')
                    ->label(__('admin.products.weight'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label(__('admin.products.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.products.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->label(__('messages.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('admin.products.status'))
                    ->options([
                        'draft'     => __('admin.products.status_draft'),
                        'pending'   => __('admin.products.status_pending'),
                        'published' => __('admin.products.status_published'),
                        'archived'  => __('admin.products.status_archived'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label(__('admin.products.bulk_publish'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $records->each(static function (Product $product): void {
                                $product->forceFill([
                                    'status'       => 'published',
                                    'is_enabled'   => true,
                                    'published_at' => $product->published_at ?? now(),
                                ])->save();
                            });

                            Notification::make()
                                ->title(__('admin.products.bulk_publish_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unpublish')
                        ->label(__('admin.products.bulk_unpublish'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each(static function (Product $product): void {
                                $product->forceFill([
                                    'status'       => 'draft',
                                    'published_at' => null,
                                ])->save();
                            });

                            Notification::make()
                                ->title(__('admin.products.bulk_unpublish_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('admin.products.bulk_feature'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records): void {
                            $records->each(static function (Product $product): void {
                                $product->forceFill([
                                    'is_featured' => true,
                                ])->save();
                            });

                            Notification::make()
                                ->title(__('admin.products.bulk_feature_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('update_stock')
                        ->label(__('admin.products.bulk_update_stock'))
                        ->icon('heroicon-o-archive-box')
                        ->form([
                            TextInput::make('stock_quantity')
                                ->label(__('admin.products.stock_quantity'))
                                ->numeric()
                                ->integer()
                                ->required(),
                            TextInput::make('low_stock_threshold')
                                ->label(__('admin.products.low_stock_threshold'))
                                ->numeric()
                                ->integer()
                                ->default(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $stockQuantity = (int) ($data['stock_quantity'] ?? 0);
                            $lowStockThreshold = (int) ($data['low_stock_threshold'] ?? 0);

                            $records->each(static function (Product $product) use ($stockQuantity, $lowStockThreshold): void {
                                $product->forceFill([
                                    'stock_quantity'      => $stockQuantity,
                                    'low_stock_threshold' => $lowStockThreshold,
                                ])->save();
                            });

                            Notification::make()
                                ->title(__('admin.products.bulk_update_stock_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('update_prices')
                        ->label(__('admin.products.bulk_update_prices'))
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('price_increase_percentage')
                                ->label(__('admin.products.price_increase_percentage'))
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $percentage = (float) ($data['price_increase_percentage'] ?? 0.0);
                            $multiplier = 1 + ($percentage / 100);

                            $records->each(static function (Product $product) use ($multiplier): void {
                                $currentPrice = (float) ($product->price ?? 0);
                                $product->price = round($currentPrice * $multiplier, 2);
                                $product->save();
                            });

                            Notification::make()
                                ->title(__('admin.products.bulk_update_prices_success'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->paginated([
                10,
                20,
                50,
                100,
                150,
                200,
                300,
                400,
                500,
                600,
                700,
                800,
                900,
                1000,
                1500,
                2000,
                3000,
                4000,
                5000,
                6000,
                7000,
                8000,
                9000,
                10000,
            ])
            ->defaultSort('created_at', 'desc');
    }
}
