<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Filament\Filters\DateRangeFilter;
use App\Support\Search\ProductSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Flatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * OrderItemResource
 *
 * Filament v4 resource for OrderItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class OrderItemResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Orders';
    }

    protected static ?string $model = OrderItem::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
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
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('orders.sections.order_items'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('order_id')
                                ->label(__('order_items.order'))
                                ->relationship('order', 'number')
                                ->searchable()
                                ->preload()
                                ->required(),
                            SearchableInput::make('product_id')
                                ->label(__('order_items.product'))
                                ->placeholder('SKU / EAN / name')
                                ->required()
                                ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?OrderItem $record): void {
                                    if ($state === null || ! $record?->product) {
                                        return;
                                    }

                                    $label = sprintf('[%s] %s', $record->product->sku ?? '—', $record->product->name ?? '');

                                    $component
                                        ->state((string) $state)
                                        ->options([
                                            (string) $record->product_id => $label,
                                        ]);
                                })
                                ->afterStateUpdated(function (?string $state, Set $set): void {
                                    if ($state === null || $state === '') {
                                        return;
                                    }

                                    $product = Product::query()
                                        ->select(['id', 'sku', 'name', 'price'])
                                        ->find((int) $state);

                                    if (! $product instanceof Product) {
                                        return;
                                    }

                                    $set('product_id', $product->getKey());

                                    $name = $product->getAttribute('name');
                                    if (is_array($name)) {
                                        $locale = app()->getLocale();
                                        $value = $name[$locale] ?? reset($name);
                                        $set('name', is_string($value) ? $value : '');
                                    } elseif (is_string($name)) {
                                        $set('name', $name);
                                    }

                                    $sku = $product->getAttribute('sku');
                                    if (is_string($sku)) {
                                        $set('sku', $sku);
                                    }

                                    $price = $product->getAttribute('price');
                                    if (is_numeric($price)) {
                                        $set('unit_price', number_format((float) $price, 2, '.', ''));
                                    }

                                    $set('product_variant_id', null);
                                }),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('product_variant_id')
                                ->label(__('order_items.product_variant'))
                                ->relationship('productVariant', 'name')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
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
                        ->schema([
                            TextInput::make('sku')
                                ->label(__('order_items.product_sku'))
                                ->maxLength(255),
                            TextInput::make('quantity')
                                ->label(__('order_items.quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $state;
                                    $total = $unitPrice * $quantity;
                                    $set('total', number_format($total, 2, '.', ''));
                                }),
                        ]),
                ]),
            Section::make(__('order_items.pricing'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('unit_price')
                                ->label(__('order_items.unit_price'))
                                ->prefix('€')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
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
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
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
                ->schema([
                    Textarea::make('notes')
                        ->label(__('order_items.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
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
                        Flatpickr::make('range')
                            ->label(__('order_items.created_at'))
                            ->rangePicker()
                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
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
            'index'  => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'view'   => Pages\ViewOrderItem::route('/{record}'),
            'edit'   => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
