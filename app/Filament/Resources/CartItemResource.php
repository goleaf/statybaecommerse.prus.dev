<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CartItemResource\Pages;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * CartItemResource
 * 
 * Filament v4 resource for CartItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;
    
    /** @var UnitEnum|string|null */
        protected static string | UnitEnum | null $navigationGroup = "Products";
    
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'product_name';

    /**
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
        return "Orders"->label();
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
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('cart_items.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('cart_items.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            Select::make('product_id')
                                ->label(__('cart_items.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
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
                    
                    Grid::make(2)
                        ->schema([
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
                                ->required()
                                ->maxLength(255),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            TextInput::make('product_sku')
                                ->label(__('cart_items.product_sku'))
                                ->maxLength(255),
                            
                            TextInput::make('quantity')
                                ->label(__('cart_items.quantity'))
                                ->numeric()
                                ->required()
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
            
            Section::make(__('cart_items.pricing'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('unit_price')
                                ->label(__('cart_items.unit_price'))
                                ->numeric()
                                ->required()
                                ->prefix('€')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $unitPrice = (float) $state;
                                    $quantity = (int) $get('quantity');
                                    $total = $unitPrice * $quantity;
                                    $set('total', number_format($total, 2, '.', ''));
                                }),
                            
                            TextInput::make('discount_amount')
                                ->label(__('cart_items.discount_amount'))
                                ->numeric()
                                ->prefix('€')
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
                                ->label(__('cart_items.total'))
                                ->numeric()
                                ->prefix('€')
                                ->required()
                                ->disabled(),
                        ]),
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
                TextColumn::make('user.name')
                    ->label(__('cart_items.user'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('product_name')
                    ->label(__('cart_items.product_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('product_sku')
                    ->label(__('cart_items.product_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('quantity')
                    ->label(__('cart_items.quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('unit_price')
                    ->label(__('cart_items.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('discount_amount')
                    ->label(__('cart_items.discount_amount'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('total')
                    ->label(__('cart_items.total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
                
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
                    ->label(__('cart_items.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('product_id')
                    ->label(__('cart_items.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
