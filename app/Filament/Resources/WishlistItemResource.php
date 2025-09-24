<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WishlistItemResource\Pages;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * WishlistItemResource
 *
 * Filament v4 resource for WishlistItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class WishlistItemResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Customers';
    }

    protected static ?string $model = WishlistItem::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-heart';
    }

    /**
     * @var UnitEnum|string|null
     */
    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'product.name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.wishlist_items.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     */
    public static function getNavigationGroupLabel(): string
    {
        return 'Customers';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.wishlist_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('admin.wishlist_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FormSection::make(__('admin.wishlist_items.sections.basic_info'))
                    ->description(__('admin.wishlist_items.sections.basic_info_description'))
                    ->schema([
                        FormGrid::make(2)
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
                        FormGrid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.wishlist_items.fields.product'))
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
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
                                        if (! $productId) {
                                            return [];
                                        }

                                        return ProductVariant::where('product_id', $productId)
                                            ->pluck('name', 'id');
                                    })
                                    ->visible(fn (callable $get) => $get('product_id') && Product::find($get('product_id'))?->variants()->exists()),
                            ]),
                        FormGrid::make(3)
                            ->schema([
                                TextInput::make('quantity')
                                    ->label(__('admin.wishlist_items.fields.quantity'))
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(999)
                                    ->required(),
                                Placeholder::make('current_price')
                                    ->label(__('admin.wishlist_items.fields.current_price'))
                                    ->content(function (callable $get) {
                                        $productId = $get('product_id');
                                        $variantId = $get('variant_id');

                                        if ($variantId) {
                                            $variant = ProductVariant::find($variantId);

                                            return $variant ? app_money_format($variant->price) : '-';
                                        } elseif ($productId) {
                                            $product = Product::find($productId);

                                            return $product ? app_money_format($product->price) : '-';
                                        }

                                        return '-';
                                    }),
                                Placeholder::make('total_price')
                                    ->label(__('admin.wishlist_items.fields.total_price'))
                                    ->content(function (callable $get) {
                                        $productId = $get('product_id');
                                        $variantId = $get('variant_id');
                                        $quantity = (int) $get('quantity') ?: 1;

                                        $price = 0;
                                        if ($variantId) {
                                            $variant = ProductVariant::find($variantId);
                                            $price = $variant ? $variant->price : 0;
                                        } elseif ($productId) {
                                            $product = Product::find($productId);
                                            $price = $product ? $product->price : 0;
                                        }

                                        return app_money_format($price * $quantity);
                                    }),
                            ]),
                        Textarea::make('notes')
                            ->label(__('admin.wishlist_items.fields.notes'))
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                FormSection::make(__('admin.wishlist_items.sections.product_info'))
                    ->description(__('admin.wishlist_items.sections.product_info_description'))
                    ->schema([
                        Placeholder::make('product_image')
                            ->label(__('admin.wishlist_items.fields.product_image'))
                            ->content(function (callable $get) {
                                $productId = $get('product_id');
                                if (! $productId) {
                                    return __('admin.wishlist_items.no_product_selected');
                                }

                                $product = Product::find($productId);
                                if (! $product || ! $product->featured_image) {
                                    return __('admin.wishlist_items.no_image');
                                }

                                return view('components.product-image', [
                                    'image' => $product->featured_image,
                                    'alt' => $product->name,
                                ])->render();
                            })
                            ->columnSpanFull(),
                        Placeholder::make('product_description')
                            ->label(__('admin.wishlist_items.fields.product_description'))
                            ->content(function (callable $get) {
                                $productId = $get('product_id');
                                if (! $productId) {
                                    return '';
                                }

                                $product = Product::find($productId);

                                return $product ? \Str::limit($product->description, 200) : '';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.featured_image')
                    ->label(__('admin.wishlist_items.fields.product_image'))
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl('/images/no-image.png'),
                TextColumn::make('product.name')
                    ->label(__('admin.wishlist_items.fields.product'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    }),
                TextColumn::make('variant.name')
                    ->label(__('admin.wishlist_items.fields.variant'))
                    ->sortable()
                    ->searchable()
                    ->placeholder(__('admin.wishlist_items.no_variant'))
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label(__('admin.wishlist_items.fields.quantity'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('current_price')
                    ->label(__('admin.wishlist_items.fields.current_price'))
                    ->getStateUsing(fn (WishlistItem $record): string => $record->formatted_current_price)
                    ->sortable()
                    ->money('EUR')
                    ->color('success'),
                TextColumn::make('total_price')
                    ->label(__('admin.wishlist_items.fields.total_price'))
                    ->getStateUsing(function (WishlistItem $record): string {
                        $price = $record->current_price ?? 0;
                        $quantity = $record->quantity ?? 1;

                        return app_money_format($price * $quantity);
                    })
                    ->sortable()
                    ->money('EUR')
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('wishlist.name')
                    ->label(__('admin.wishlist_items.fields.wishlist'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('secondary')
                    ->toggleable(),
                TextColumn::make('wishlist.user.name')
                    ->label(__('admin.wishlist_items.fields.user'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('wishlist.user.email')
                    ->label(__('admin.wishlist_items.fields.user_email'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label(__('admin.wishlist_items.fields.notes'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.category.name')
                    ->label(__('admin.wishlist_items.fields.category'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.brand.name')
                    ->label(__('admin.wishlist_items.fields.brand'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('product.is_active')
                    ->label(__('admin.wishlist_items.fields.product_status'))
                    ->boolean()
                    ->getStateUsing(fn (WishlistItem $record): bool => $record->product?->is_active ?? false)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.wishlist_items.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        true: fn (Builder $query) => $query->whereNotNull('variant_id'),
                        false: fn (Builder $query) => $query->whereNull('variant_id'),
                    ),
                Filter::make('user_id')
                    ->form([
                        Select::make('user_id')
                            ->label(__('admin.wishlist_items.filters.user'))
                            ->options(\App\Models\User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->query(function (Builder $query, array $data): Builder {
                        $userId = $data['user_id'] ?? null;
                        return $query->when($userId, fn (Builder $q): Builder => $q->whereHas('wishlist', fn (Builder $w): Builder => $w->where('user_id', $userId)));
                    }),
                Filter::make('category_id')
                    ->form([
                        Select::make('category_id')
                            ->label(__('admin.wishlist_items.filters.category'))
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->query(function (Builder $query, array $data): Builder {
                        $categoryId = $data['category_id'] ?? null;
                        return $query->when($categoryId, fn (Builder $q): Builder => $q->whereHas('product', fn (Builder $p): Builder => $p->where('category_id', $categoryId)));
                    }),
                Filter::make('brand_id')
                    ->form([
                        Select::make('brand_id')
                            ->label(__('admin.wishlist_items.filters.brand'))
                            ->options(Brand::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->query(function (Builder $query, array $data): Builder {
                        $brandId = $data['brand_id'] ?? null;
                        return $query->when($brandId, fn (Builder $q): Builder => $q->whereHas('product', fn (Builder $p): Builder => $p->where('brand_id', $brandId)));
                    }),
                TernaryFilter::make('product.is_active')
                    ->label(__('admin.wishlist_items.filters.active_products'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('product', fn ($q) => $q->where('is_active', true)),
                        false: fn (Builder $query) => $query->whereHas('product', fn ($q) => $q->where('is_active', false)),
                    ),
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label(__('admin.wishlist_items.filters.created_from')),
                        DateTimePicker::make('created_until')
                            ->label(__('admin.wishlist_items.filters.created_until')),
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
                Filter::make('price_range')
                    ->form([
                        TextInput::make('min_price')
                            ->label(__('admin.wishlist_items.filters.min_price'))
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('max_price')
                            ->label(__('admin.wishlist_items.filters.max_price'))
                            ->numeric()
                            ->step(0.01),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->whereHas('product', fn ($q) => $q->where('price', '>=', $price)),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->whereHas('product', fn ($q) => $q->where('price', '<=', $price)),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('move_to_cart')
                        ->label(__('admin.wishlist_items.actions.move_to_cart'))
                        ->icon('heroicon-o-shopping-cart')
                        ->color('success')
                        ->action(function (WishlistItem $record): void {
                            try {
                                // Create cart item logic here
                                CartItem::create([
                                    'user_id' => $record->wishlist->user_id,
                                    'product_id' => $record->product_id,
                                    'variant_id' => $record->variant_id,
                                    'quantity' => $record->quantity,
                                ]);

                                FilamentNotification::make()
                                    ->title(__('admin.wishlist_items.moved_to_cart_successfully'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                FilamentNotification::make()
                                    ->title(__('admin.wishlist_items.move_to_cart_error'))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation(),
                    Action::make('duplicate')
                        ->label(__('admin.wishlist_items.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (WishlistItem $record): void {
                            $record->replicate()->save();

                            FilamentNotification::make()
                                ->title(__('admin.wishlist_items.duplicated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('move_to_cart')
                        ->label(__('admin.wishlist_items.actions.move_to_cart'))
                        ->icon('heroicon-o-shopping-cart')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            try {
                                $moved = 0;
                                foreach ($records as $record) {
                                    CartItem::create([
                                        'user_id' => $record->wishlist->user_id,
                                        'product_id' => $record->product_id,
                                        'variant_id' => $record->variant_id,
                                        'quantity' => $record->quantity,
                                    ]);
                                    $moved++;
                                }

                                FilamentNotification::make()
                                    ->title(__('admin.wishlist_items.bulk_moved_to_cart_successfully', ['count' => $moved]))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                FilamentNotification::make()
                                    ->title(__('admin.wishlist_items.bulk_move_to_cart_error'))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation(),
                    TableBulkAction::make('duplicate')
                        ->label(__('admin.wishlist_items.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $duplicated = 0;
                            foreach ($records as $record) {
                                $record->replicate()->save();
                                $duplicated++;
                            }

                            FilamentNotification::make()
                                ->title(__('admin.wishlist_items.bulk_duplicated_successfully', ['count' => $duplicated]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListWishlistItems::route('/'),
            'create' => Pages\CreateWishlistItem::route('/create'),
            'view' => Pages\ViewWishlistItem::route('/{record}'),
            'edit' => Pages\EditWishlistItem::route('/{record}/edit'),
        ];
    }
}
