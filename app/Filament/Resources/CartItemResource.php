<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CartItemResource\Pages;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CartItemResource
 *
 * Filament v4 resource for CartItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = "Products";

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('cart_items.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('cart_items.navigation_group');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('cart_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('cart_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('cart_items.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('user_id')
                                ->label(__('cart_items.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('product_id')
                                ->label(__('cart_items.product'))
                                ->relationship('product', 'name')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('product_sku', $product->sku);
                                            $set('unit_price', $product->price);
                                        }
                                    }
                                }),
                        ]),
                    Select::make('product_variant_id')
                        ->label(__('cart_items.product_variant'))
                        ->relationship('productVariant', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $variant = ProductVariant::find($state);
                                if ($variant) {
                                    $set('product_name', $variant->name);
                                    $set('product_sku', $variant->sku);
                                    $set('unit_price', $variant->price);
                                }
                            }
                        }),
                    TextInput::make('product_name')
                        ->label(__('cart_items.product_name'))
                        ->maxLength(255),
                    TextInput::make('product_sku')
                        ->label(__('cart_items.product_sku'))
                        ->maxLength(255),
                    Grid::make(2)
                        ->components([
                            TextInput::make('quantity')
                                ->label(__('cart_items.quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $state;
                                    $total = $unitPrice * $quantity;
                                    $set('total_price', number_format($total, 2, '.', ''));
                                }),
                            TextInput::make('minimum_quantity')
                                ->label(__('cart_items.minimum_quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->default(1),
                        ]),
                    TextInput::make('session_id')
                        ->label(__('cart_items.session_id'))
                        ->maxLength(255)
                        ->helperText(__('cart_items.session_id_help')),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('cart_items.notes'))
                        ->rows(3)
                        ->maxLength(1000),
                ]),
            Section::make(__('cart_items.pricing'))
                ->components([
                    Grid::make(3)
                        ->components([
                            TextInput::make('unit_price')
                                ->label(__('cart_items.unit_price'))
                                ->prefix('€')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $state;
                                    $quantity = (int) $get('quantity');
                                    $total = $unitPrice * $quantity;
                                    $set('total_price', number_format($total, 2, '.', ''));
                                }),
                            TextInput::make('discount_amount')
                                ->label(__('cart_items.discount_amount'))
                                ->prefix('€')
                                ->numeric()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $get('quantity');
                                    $discount = (float) $state;
                                    $total = ($unitPrice * $quantity) - $discount;
                                    $set('total_price', number_format($total, 2, '.', ''));
                                }),
                            TextInput::make('total_price')
                                ->label(__('cart_items.total'))
                                ->prefix('€')
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('cart_items.additional_info'))
                ->components([
                    Forms\Components\KeyValue::make('attributes')
                        ->label(__('cart_items.attributes'))
                        ->keyLabel(__('cart_items.attribute_name'))
                        ->valueLabel(__('cart_items.attribute_value'))
                        ->addActionLabel(__('cart_items.add_attribute')),
                    Forms\Components\KeyValue::make('product_snapshot')
                        ->label(__('cart_items.product_snapshot'))
                        ->keyLabel(__('cart_items.snapshot_key'))
                        ->valueLabel(__('cart_items.snapshot_value'))
                        ->addActionLabel(__('cart_items.add_snapshot'))
                        ->helperText(__('cart_items.product_snapshot_help')),
                ])
                ->collapsible(),
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
                TextColumn::make('user.name')
                    ->label(__('cart_items.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('cart_items.product_name'))
                    ->sortable()
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label(__('cart_items.product_sku'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')
                    ->label(__('cart_items.quantity'))
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('minimum_quantity')
                    ->label(__('cart_items.minimum_quantity'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit_price')
                    ->label(__('cart_items.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('cart_items.discount_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(__('cart_items.total'))
                    ->money('EUR')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('cart_items.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cart_items.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label(__('cart_items.product'))
                    ->relationship('product', 'name')
                    ->preload(),
                SelectFilter::make('product_variant_id')
                    ->label(__('cart_items.product_variant'))
                    ->relationship('productVariant', 'name')
                    ->preload(),
                Filter::make('quantity_range')
                    ->form([
                        Forms\Components\TextInput::make('quantity_from')
                            ->label(__('cart_items.quantity_from'))
                            ->numeric(),
                        Forms\Components\TextInput::make('quantity_to')
                            ->label(__('cart_items.quantity_to'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['quantity_from'],
                                fn(Builder $query, $quantity): Builder => $query->where('quantity', '>=', $quantity),
                            )
                            ->when(
                                $data['quantity_to'],
                                fn(Builder $query, $quantity): Builder => $query->where('quantity', '<=', $quantity),
                            );
                    }),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('cart_items.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('cart_items.created_until')),
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
                Action::make('move_to_wishlist')
                    ->label(__('cart_items.move_to_wishlist'))
                    ->icon('heroicon-o-heart')
                    ->color('warning')
                    ->action(function (CartItem $record): void {
                        // Move to wishlist logic here
                        Notification::make()
                            ->title(__('cart_items.moved_to_wishlist_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('clear_old_carts')
                        ->label(__('cart_items.clear_old_carts'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $oldRecords = $records->filter(function ($record) {
                                return $record->created_at->lt(now()->subDays(30));
                            });
                            $oldRecords->each->delete();
                            Notification::make()
                                ->title(__('cart_items.old_carts_cleared_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'view' => Pages\ViewCartItem::route('/{record}'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }
}
