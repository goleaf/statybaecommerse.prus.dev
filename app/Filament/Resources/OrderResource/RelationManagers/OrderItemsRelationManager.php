<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'orders.order_items';

    protected static ?string $modelLabel = 'orders.order_item';

    protected static ?string $pluralModelLabel = 'orders.order_items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('orders.item_information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label('orders.product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
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
                                    ->label('orders.product_variant')
                                    ->relationship('productVariant', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $variant = ProductVariant::find($state);
                                            if ($variant) {
                                                $set('unit_price', $variant->price);
                                            }
                                        }
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('orders.product_name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('sku')
                                    ->label('orders.sku')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label('orders.quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $unitPrice = $get('unit_price');
                                        if ($unitPrice && $state) {
                                            $set('total', $unitPrice * $state);
                                        }
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('unit_price')
                                    ->label('orders.unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $quantity = $get('quantity');
                                        if ($quantity && $state) {
                                            $set('total', $state * $quantity);
                                        }
                                    }),

                                TextInput::make('price')
                                    ->label('orders.price')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('unit_price', $state);
                                        }
                                    }),

                                TextInput::make('total')
                                    ->label('orders.total')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->required()
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('orders.product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('orders.sku')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('orders.quantity')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('unit_price')
                    ->label('orders.unit_price')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('orders.total')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
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
}

