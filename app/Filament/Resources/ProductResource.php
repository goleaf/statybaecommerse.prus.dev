<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\FeaturesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\RequestsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\SimilaritiesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\ProductResource\Schemas\ProductForm;
use App\Filament\Resources\ProductResource\Schemas\ProductInfolist;
use App\Models\Product;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.products.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.products.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.products.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('messages.image'))
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
                TextColumn::make('compare_price')
                    ->label(__('admin.products.compare_price'))
                    ->money('EUR')
                    ->sortable()
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
                TernaryFilter::make('is_visible')
                    ->label(__('admin.products.is_visible')),
            ])
            ->actions([
                ViewAction::make(),
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
                                    'is_visible'   => true,
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
                                    'status'     => 'draft',
                                    'is_visible' => false,
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
            ImagesRelationManager::class,
            FeaturesRelationManager::class,
            RequestsRelationManager::class,
            SimilaritiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
