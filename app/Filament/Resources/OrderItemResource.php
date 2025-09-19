<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * OrderItemResource
 *
 * Filament v4 resource for OrderItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    // protected static $navigationGroup = NavigationGroup::Orders;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('orders.models.order_items');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('orders.models.order_items');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('orders.models.order_item');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('orders.sections.order_items'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('order_id')
                                ->label(__('order_items.order'))
                                ->relationship('order', 'number')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('product_id')
                                ->label(__('order_items.product'))
                                ->relationship('product', 'name')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('name', $product->name);
                                            $set('sku', $product->sku);
                                            $set('unit_price', $product->price);
                                        }
                                    }
                                }),
                        ]),
                    Grid::make(2)
                        ->components([
                            Select::make('product_variant_id')
                                ->label(__('order_items.product_variant'))
                                ->relationship('productVariant', 'name')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $variant = ProductVariant::find($state);
                                        if ($variant) {
                                            $set('name', $variant->name);
                                            $set('sku', $variant->sku);
                                            $set('unit_price', $variant->price);
                                        }
                                    }
                                }),
                            TextInput::make('name')
                                ->label(__('order_items.product_name'))
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('sku')
                                ->label(__('order_items.product_sku'))
                                ->maxLength(255),
                            TextInput::make('quantity')
                                ->label(__('order_items.quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $state;
                                    $total = $unitPrice * $quantity;
                                    $set('total', number_format($total, 2, '.', ''));
                                }),
                        ]),
                ]),
            Section::make(__('order_items.pricing'))
                ->components([
                    Grid::make(3)
                        ->components([
                            TextInput::make('unit_price')
                                ->label(__('order_items.unit_price'))
                                ->prefix('€')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $state;
                                    $quantity = (int) $get('quantity');
                                    $total = $unitPrice * $quantity;
                                    $set('total', number_format($total, 2, '.', ''));
                                }),
                            TextInput::make('discount_amount')
                                ->label(__('order_items.discount_amount'))
                                ->prefix('€')
                                ->numeric()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $get('quantity');
                                    $discount = (float) $state;
                                    $total = ($unitPrice * $quantity) - $discount;
                                    $set('total', number_format($total, 2, '.', ''));
                                }),
                            TextInput::make('total')
                                ->label(__('order_items.total'))
                                ->prefix('€')
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('order_items.additional_information'))
                ->components([
                    Textarea::make('notes')
                        ->label(__('order_items.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.number')
                    ->label(__('order_items.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('order_items.product_name'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('order_items.product_sku'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('order_items.quantity'))
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('unit_price')
                    ->label(__('order_items.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('order_items.discount_amount'))
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label(__('order_items.total'))
                    ->money('EUR')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('order_items.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('order_items.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label(__('order_items.order'))
                    ->relationship('order', 'number')
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label(__('order_items.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('order_items.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('order_items.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'view' => Pages\ViewOrderItem::route('/{record}'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
