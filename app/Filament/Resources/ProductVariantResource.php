<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Filament\Resources\ProductVariantResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\InventoriesRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\PricesRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\SimilaritiesRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\StockMovementsRelationManager;
use App\Filament\Resources\ProductVariantResource\RelationManagers\VariantCombinationsRelationManager;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantInfolist;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

final class ProductVariantResource extends BaseResource
{
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.product_variants');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.product_variants');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.product_variant');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductVariantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductVariantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('admin.product_variants.no_product_variants'))
            ->columns([
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('messages.stock_quantity'))
                    ->sortable(),
                ToggleColumn::make('is_enabled')
                    ->sortable()
                    ->label(__('messages.is_enabled')),
            ])
            ->filters([
                SelectFilter::make('stock_status')
                    ->label(__('admin.labels.stock_status'))
                    ->options([
                        'in_stock'     => __('admin.product_variants.in_stock'),
                        'low_stock'    => __('admin.product_variants.low_stock'),
                        'out_of_stock' => __('admin.product_variants.out_of_stock'),
                        'not_tracked'  => __('admin.product_variants.not_tracked'),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        $selected = $data['value'] ?? null;
                        $availableExpression = '(COALESCE(stock_quantity, 0) - COALESCE(reserved_quantity, 0))';

                        return match ($selected) {
                            'in_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw($availableExpression . ' > COALESCE(low_stock_threshold, 0)'),
                            'low_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw($availableExpression . ' > 0')
                                ->whereRaw($availableExpression . ' <= COALESCE(low_stock_threshold, 0)'),
                            'out_of_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw($availableExpression . ' <= 0'),
                            'not_tracked' => $query
                                ->where('track_inventory', false),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        $relations = [
            ProductsRelationManager::class,
            PricesRelationManager::class,
            InventoriesRelationManager::class,
            AttributesRelationManager::class,
            ImagesRelationManager::class,
            OrdersRelationManager::class,
            StockMovementsRelationManager::class,
            SimilaritiesRelationManager::class,
            VariantCombinationsRelationManager::class,
        ];

        if (SchemaFacade::hasTable('discounts') && SchemaFacade::hasTable('discount_products')) {
            $relations[] = DiscountsRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariants::route('/create'),
            'view'   => Pages\ViewProductVariants::route('/{record}'),
            'edit'   => Pages\EditProductVariants::route('/{record}/edit'),
        ];
    }
}
