<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

final class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.order_item.basic_information'))
                    ->schema([
                        Select::make('order_id')
                            ->label(__('admin.order_item.order'))
                            ->relationship('order', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('product_id')
                            ->label(__('admin.order_item.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('name', $product->name);
                                        $set('sku', $product->sku);
                                        $set('unit_price', $product->price);
                                    }
                                }
                            }),
                        
                        Select::make('product_variant_id')
                            ->label(__('admin.order_item.product_variant'))
                            ->relationship('productVariant', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
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
                            ->label(__('admin.order_item.name'))
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('sku')
                            ->label(__('admin.order_item.sku'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Section::make(__('admin.order_item.pricing_and_quantity'))
                    ->schema([
                        TextInput::make('quantity')
                            ->label(__('admin.order_item.quantity'))
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                $unitPrice = (float) $get('unit_price');
                                $quantity = (int) $state;
                                $set('total', $unitPrice * $quantity);
                            }),
                        
                        TextInput::make('unit_price')
                            ->label(__('admin.order_item.unit_price'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                $quantity = (int) $get('quantity');
                                $unitPrice = (float) $state;
                                $set('total', $unitPrice * $quantity);
                            }),
                        
                        TextInput::make('price')
                            ->label(__('admin.order_item.price'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        
                        TextInput::make('total')
                            ->label(__('admin.order_item.total'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.order_item.id'))
                    ->sortable(),
                
                TextColumn::make('order.id')
                    ->label(__('admin.order_item.order'))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('product.name')
                    ->label(__('admin.order_item.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('productVariant.name')
                    ->label(__('admin.order_item.product_variant'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('name')
                    ->label(__('admin.order_item.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('sku')
                    ->label(__('admin.order_item.sku'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('quantity')
                    ->label(__('admin.order_item.quantity'))
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('unit_price')
                    ->label(__('admin.order_item.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('price')
                    ->label(__('admin.order_item.price'))
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('total')
                    ->label(__('admin.order_item.total'))
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label(__('admin.order_item.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('admin.order_item.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label(__('admin.order_item.order'))
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('product_id')
                    ->label(__('admin.order_item.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('product_variant_id')
                    ->label(__('admin.order_item.product_variant'))
                    ->relationship('productVariant', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

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
