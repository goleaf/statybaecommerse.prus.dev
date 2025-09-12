<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CartItemResource\Pages;
use App\Filament\Resources\CartItemResource\RelationManagers;
use App\Filament\Resources\CartItemResource\Widgets;
use App\Models\CartItem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-shopping-bag';

    /**
     * @var UnitEnum|string|null
     */
    protected static $navigationGroup = 'admin.navigation.sales';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.cart_items');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.cart_item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.cart_items');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.sections.basic_information'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('admin.cart_items.fields.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.users.fields.name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('admin.users.fields.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Select::make('product_id')
                            ->label(__('admin.cart_items.fields.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('unit_price', $product->price);
                                        $set('minimum_quantity', $product->minimum_quantity ?? 1);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('variant_id')
                            ->label(__('admin.cart_items.fields.variant'))
                            ->relationship('variant', 'name')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $variant = \App\Models\ProductVariant::find($state);
                                    if ($variant) {
                                        $set('unit_price', $variant->price);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('session_id')
                            ->label(__('admin.cart_items.fields.session_id'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.cart_items.fields.quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $unitPrice = $get('unit_price');
                                if ($state && $unitPrice) {
                                    $set('total_price', $state * $unitPrice);
                                }
                            }),
                        Forms\Components\TextInput::make('minimum_quantity')
                            ->label(__('admin.cart_items.fields.minimum_quantity'))
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->label(__('admin.cart_items.fields.unit_price'))
                            ->numeric()
                            ->required()
                            ->prefix('€')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $quantity = $get('quantity');
                                if ($state && $quantity) {
                                    $set('total_price', $state * $quantity);
                                }
                            }),
                        Forms\Components\TextInput::make('total_price')
                            ->label(__('admin.cart_items.fields.total_price'))
                            ->numeric()
                            ->required()
                            ->prefix('€')
                            ->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.sections.additional_information'))
                    ->schema([
                        Forms\Components\KeyValue::make('product_snapshot')
                            ->label(__('admin.cart_items.fields.product_snapshot'))
                            ->keyLabel(__('admin.cart_items.fields.attribute_name'))
                            ->valueLabel(__('admin.cart_items.fields.attribute_value'))
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.cart_items.fields.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image')
                    ->label(__('admin.cart_items.fields.image'))
                    ->getStateUsing(fn (CartItem $record) => $record->product?->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.cart_items.fields.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.cart_items.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->limit(30),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label(__('admin.cart_items.fields.variant'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label(__('admin.cart_items.fields.sku'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.common.copied'))
                    ->weight('mono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('admin.cart_items.fields.quantity'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (CartItem $record) => $record->needsRestocking() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('minimum_quantity')
                    ->label(__('admin.cart_items.fields.minimum_quantity'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('admin.cart_items.fields.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('admin.cart_items.fields.total_price'))
                    ->money('EUR')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('session_id')
                    ->label(__('admin.cart_items.fields.session_id'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20),
                Tables\Columns\IconColumn::make('needs_restocking')
                    ->label(__('admin.cart_items.fields.needs_restocking'))
                    ->boolean()
                    ->getStateUsing(fn (CartItem $record) => $record->needsRestocking())
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.cart_items.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.cart_items.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('admin.cart_items.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('variant')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('needs_restocking')
                    ->label(__('admin.cart_items.filters.needs_restocking'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereRaw('quantity < minimum_quantity'),
                        false: fn (Builder $query) => $query->whereRaw('quantity >= minimum_quantity'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.cart_items.filters.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.cart_items.filters.created_until')),
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
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label(__('admin.cart_items.filters.min_price'))
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('max_price')
                            ->label(__('admin.cart_items.filters.max_price'))
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('total_price', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('total_price', '<=', $price),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\Action::make('update_quantity')
                    ->label(__('admin.cart_items.actions.update_quantity'))
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('admin.cart_items.fields.quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (CartItem $record, array $data): void {
                        $record->update(['quantity' => $data['quantity']]);
                        $record->updateTotalPrice();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_prices')
                        ->label(__('admin.cart_items.actions.update_prices'))
                        ->icon('heroicon-o-currency-euro')
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->updateTotalPrice();
                            }
                        }),
                    Tables\Actions\BulkAction::make('clear_old_carts')
                        ->label(__('admin.cart_items.actions.clear_old_carts'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->where('created_at', '<', now()->subDays(30))->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductRelationManager::class,
            RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'view' => Pages\ViewCartItem::route('/{record}'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CartItemsOverviewWidget::class,
            Widgets\CartItemsChartWidget::class,
            Widgets\LowStockCartItemsWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // Authorization methods
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrator') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view cart items') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create cart items') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update cart items') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete cart items') ?? false;
    }
}
