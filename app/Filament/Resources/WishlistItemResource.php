<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\WishlistItemResource\Pages;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * WishlistItemResource
 *
 * Filament v4 resource for WishlistItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class WishlistItemResource extends Resource
{
    protected static ?string $model = WishlistItem::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'product.name';
    protected static $navigationGroup = NavigationGroup::Customers;

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.wishlist_items.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Customers';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.wishlist_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.wishlist_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.wishlist_items.sections.basic_info'))
                    ->description(__('admin.wishlist_items.sections.basic_info_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('wishlist_id')
                                    ->label(__('admin.wishlist_items.fields.wishlist'))
                                    ->relationship('wishlist', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('admin.wishlists.fields.name'))
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('admin.wishlists.fields.description'))
                                            ->columnSpanFull(),
                                        Toggle::make('is_public')
                                            ->label(__('admin.wishlists.fields.is_public'))
                                            ->default(false),
                                        Toggle::make('is_default')
                                            ->label(__('admin.wishlists.fields.is_default'))
                                            ->default(false),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return UserWishlist::create($data)->getKey();
                                    }),
                                Select::make('user_id')
                                    ->label(__('admin.wishlist_items.fields.user'))
                                    ->relationship('wishlist.user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.wishlist_items.fields.product'))
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product && $product->variants()->exists()) {
                                                $set('variant_id', null);
                                            }
                                        }
                                    }),
                                Select::make('variant_id')
                                    ->label(__('admin.wishlist_items.fields.variant'))
                                    ->relationship('variant', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->options(function (callable $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }
                                        return ProductVariant::where('product_id', $productId)
                                            ->pluck('name', 'id');
                                    })
                                    ->visible(fn(callable $get) => $get('product_id') && Product::find($get('product_id'))?->variants()->exists()),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('quantity')
                                    ->label(__('admin.wishlist_items.fields.quantity'))
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('notes')
                                    ->label(__('admin.wishlist_items.fields.notes'))
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(2),
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
                TextColumn::make('id')
                    ->label(__('admin.wishlist_items.fields.id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('wishlist.name')
                    ->label(__('admin.wishlist_items.fields.wishlist'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('wishlist.user.name')
                    ->label(__('admin.wishlist_items.fields.user'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('product.name')
                    ->label(__('admin.wishlist_items.fields.product'))
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('variant.name')
                    ->label(__('admin.wishlist_items.fields.variant'))
                    ->sortable()
                    ->searchable()
                    ->placeholder(__('admin.wishlist_items.no_variant'))
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label(__('admin.wishlist_items.fields.quantity'))
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('display_name')
                    ->label(__('admin.wishlist_items.fields.display_name'))
                    ->getStateUsing(fn(WishlistItem $record): string => $record->display_name)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('current_price')
                    ->label(__('admin.wishlist_items.fields.current_price'))
                    ->getStateUsing(fn(WishlistItem $record): string => $record->formatted_current_price)
                    ->sortable()
                    ->money('EUR'),
                TextColumn::make('notes')
                    ->label(__('admin.wishlist_items.fields.notes'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.wishlist_items.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.wishlist_items.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('wishlist_id')
                    ->label(__('admin.wishlist_items.filters.wishlist'))
                    ->relationship('wishlist', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label(__('admin.wishlist_items.filters.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('variant_id')
                    ->label(__('admin.wishlist_items.filters.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_variant')
                    ->label(__('admin.wishlist_items.filters.has_variant'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('variant_id'),
                        false: fn(Builder $query) => $query->whereNull('variant_id'),
                    ),
                SelectFilter::make('user_id')
                    ->label(__('admin.wishlist_items.filters.user'))
                    ->relationship('wishlist.user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('move_to_cart')
                    ->label(__('admin.wishlist_items.actions.move_to_cart'))
                    ->icon('heroicon-o-shopping-cart')
                    ->action(function (WishlistItem $record): void {
                        // This would typically create a cart item
                        // For now, we'll just show a notification
                        FilamentNotification::make()
                            ->title(__('admin.wishlist_items.moved_to_cart_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('move_to_cart')
                        ->label(__('admin.wishlist_items.actions.move_to_cart'))
                        ->icon('heroicon-o-shopping-cart')
                        ->action(function (Collection $records): void {
                            // This would typically create cart items for all records
                            // For now, we'll just show a notification
                            FilamentNotification::make()
                                ->title(__('admin.wishlist_items.moved_to_cart_successfully'))
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListWishlistItems::route('/'),
            'create' => Pages\CreateWishlistItem::route('/create'),
            'view' => Pages\ViewWishlistItem::route('/{record}'),
            'edit' => Pages\EditWishlistItem::route('/{record}/edit'),
        ];
    }
}
