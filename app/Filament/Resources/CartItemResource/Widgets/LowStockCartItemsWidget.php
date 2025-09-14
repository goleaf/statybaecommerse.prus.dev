<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\Widgets;

use App\Models\CartItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * LowStockCartItemsWidget
 * 
 * Filament v4 resource for LowStockCartItemsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class LowStockCartItemsWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.cart_items.widgets.low_stock_items';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(CartItem::query()->whereRaw('quantity < minimum_quantity')->with(['user', 'product'])->latest())->columns([Tables\Columns\ImageColumn::make('product.image')->label(__('admin.cart_items.fields.image'))->getStateUsing(fn(CartItem $record) => $record->product?->getFirstMediaUrl('images', 'thumb'))->defaultImageUrl(asset('images/placeholder-product.png'))->circular()->size(40), Tables\Columns\TextColumn::make('product.name')->label(__('admin.cart_items.fields.product'))->searchable()->sortable()->weight('medium')->limit(30), Tables\Columns\TextColumn::make('user.name')->label(__('admin.cart_items.fields.user'))->searchable()->sortable()->toggleable(), Tables\Columns\TextColumn::make('quantity')->label(__('admin.cart_items.fields.quantity'))->numeric()->badge()->color('danger'), Tables\Columns\TextColumn::make('minimum_quantity')->label(__('admin.cart_items.fields.minimum_quantity'))->numeric()->badge()->color('warning'), Tables\Columns\TextColumn::make('total_price')->label(__('admin.cart_items.fields.total_price'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('admin.cart_items.fields.created_at'))->dateTime()->sortable()])->actions([Tables\Actions\Action::make('update_quantity')->label(__('admin.cart_items.actions.update_quantity'))->icon('heroicon-o-pencil')->form([\Filament\Forms\Components\TextInput::make('quantity')->label(__('admin.cart_items.fields.quantity'))->numeric()->required()->minValue(1)])->action(function (CartItem $record, array $data): void {
            $record->update(['quantity' => $data['quantity']]);
            $record->updateTotalPrice();
        })])->paginated([10, 25, 50])->defaultPaginationPageOption(10);
    }
}