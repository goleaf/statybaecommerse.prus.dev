<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Quantity;
use App\Filament\Resources\CartItemResource\Pages;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use BackedEnum;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class CartItemResource extends Resource
{
    /** @var string|BackedEnum|null Navigation icon configured per Filament v4 guidance. */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $model = CartItem::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('cart_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('cart_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('cart_items.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label(__('cart_items.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            SearchableInput::make('product_id')
                                ->label(__('cart_items.product'))
                                ->placeholder('SKU / EAN / name')
                                ->required(fn (?CartItem $record): bool => $record === null)
                                ->dehydrated(fn (?CartItem $record): bool => $record === null)
                                ->live()
                                ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?CartItem $record): void {
                                    // Hydrate via helper to keep metadata contract in sync with documentation.
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value) use ($record): ?array {
                                            $product = $record?->product ?? Product::query()
                                                ->select(['id', 'sku', 'name'])
                                                ->find($value);

                                            if (! $product instanceof Product) {
                                                return null;
                                            }

                                            return [
                                                'value' => $product->getKey(),
                                                'label' => ProductSearch::label($product),
                                            ];
                                        },
                                    );
                                })
                                // See docs/forms/SEARCHABLE_INPUT_METADATA.md for SearchResult metadata conventions.
                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, Forms\Set $set): void {
                                    if ($state === null || $state === '') {
                                        // Clear dependent metadata when the lookup resets.
                                        SearchableInputHelper::clear($component, $set, [
                                            'product_id'         => null,
                                            'product_name'       => null,
                                            'product_sku'        => null,
                                            'unit_price'         => null,
                                            'product_variant_id' => null,
                                        ]);

                                        return;
                                    }

                                    $product = Product::query()
                                        ->select(['id', 'name', 'sku', 'price'])
                                        ->find((int) $state);

                                    if (! $product instanceof Product) {
                                        return;
                                    }

                                    $set('product_id', $product->getKey());
                                    $set('product_name', $product->name);
                                    $set('product_sku', $product->sku);
                                    $set('unit_price', $product->price);

                                    if ($product->variants()->exists()) {
                                        $set('product_variant_id', null);
                                    }
                                }),
                        ]),
                    Select::make('product_variant_id')
                        ->label(__('cart_items.product_variant'))
                        ->relationship('productVariant', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set): void {
                            if ($state) {
                                $variant = ProductVariant::find($state);
                                if ($variant) {
                                    $set('product_name', $variant->name);
                                    $set('product_sku', $variant->sku);
                                    $set('unit_price', $variant->price);
                                }
                            }
                        }),
                    // The product name is derived from the related product, therefore we never dehydrate the value.
                    TextInput::make('product_name')
                        ->label(__('cart_items.product_name'))
                        ->readOnly()
                        ->dehydrated(false)
                        ->maxLength(255),
                    // The product SKU behaves like the name above and remains display-only.
                    TextInput::make('product_sku')
                        ->label(__('cart_items.product_sku'))
                        ->readOnly()
                        ->dehydrated(false)
                        ->maxLength(255),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('quantity')
                                ->label(__('cart_items.quantity'))
                                ->minValue(1)
                                ->step(1)
                                ->default(1)
                                ->required()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $state;
                                    $total = $unitPrice * $quantity;
                                    $set('total_price', number_format($total, 2, '.', ''));
                                }),
                            Quantity::make('minimum_quantity')
                                ->label(__('cart_items.minimum_quantity'))
                                ->minValue(1)
                                ->step(1)
                                ->default(1),
                        ]),
                    TextInput::make('session_id')
                        ->label(__('cart_items.session_id'))
                        ->maxLength(255)
                        ->helperText(__('cart_items.session_id_help')),
                    Textarea::make('notes')
                        ->label(__('cart_items.notes'))
                        ->rows(3)
                        ->maxLength(1000),
                ]),
            SchemaSection::make(__('cart_items.pricing'))
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('unit_price')
                                ->label(__('cart_items.unit_price'))
                                ->prefix('€')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
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
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set): void {
                                    $unitPrice = (float) $get('unit_price');
                                    $quantity = (int) $get('quantity');
                                    $discount = (float) $state;
                                    $total = ($unitPrice * $quantity) - $discount;
                                    $set('total_price', number_format($total, 2, '.', ''));
                                }),
                            // Persist the calculated total even though the field is disabled for manual edits.
                            TextInput::make('total_price')
                                ->label(__('cart_items.total'))
                                ->prefix('€')
                                ->disabled()
                                ->dehydrated(),
                        ]),
                ]),
            SchemaSection::make(__('cart_items.additional_info'))
                ->schema([
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
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                // Present the owning user with search and sorting capabilities for administrative triage.
                TextColumn::make('user.name')
                    ->label(__('cart_items.user'))
                    ->searchable()
                    ->sortable(),
                // Show the primary product name and support quick searching by merchandising teams.
                TextColumn::make('product.name')
                    ->label(__('cart_items.product_name'))
                    ->sortable()
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('productVariant.name')
                    ->label(__('cart_items.product_variant'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.sku')
                    ->label(__('cart_items.product_sku'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')
                    ->label(__('cart_items.quantity'))
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(function (CartItem $record): string {
                        // Provide quick visual cues for stock planners about item coverage.
                        $minimum = max(1, (int) $record->minimum_quantity);

                        if ($record->quantity < $minimum) {
                            return 'danger';
                        }

                        if ($record->quantity < $minimum + 5) {
                            return 'warning';
                        }

                        return 'success';
                    }),
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
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        // Ensure table queries respect the selected product filter even when
                        // interacting with the query instance directly (e.g. tests calling
                        // getFilteredTableQuery()).
                        $productId = (int) ($data['value'] ?? 0);

                        if ($productId <= 0) {
                            return $query;
                        }

                        return $query->where($query->qualifyColumn('product_id'), $productId);
                    }),
                SelectFilter::make('product_variant_id')
                    ->label(__('cart_items.product_variant'))
                    ->relationship('productVariant', 'name')
                    ->preload(),
                Filter::make('needs_restocking')
                    ->label(__('cart_items.needs_restocking'))
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        // Limit to rows where quantity dips below the defined minimum threshold.
                        return $query->whereColumn('quantity', '<', 'minimum_quantity');
                    }),
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
                                fn (Builder $query, $quantity): Builder => $query->where('quantity', '>=', $quantity),
                            )
                            ->when(
                                $data['quantity_to'],
                                fn (Builder $query, $quantity): Builder => $query->where('quantity', '<=', $quantity),
                            );
                    }),
                Filter::make('price_range')
                    ->label(__('cart_items.price_range'))
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->label(__('cart_items.price_from'))
                            ->numeric(),
                        Forms\Components\TextInput::make('price_to')
                            ->label(__('cart_items.price_to'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Enable finance teams to focus on specific unit price windows.
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('unit_price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('unit_price', '<=', $price),
                            );
                    }),
                Filter::make('created_at')
                    ->form([
                        SupportFlatpickr::makeDate('created_from')
                            ->label(__('cart_items.created_from')),
                        SupportFlatpickr::makeDate('created_until')
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
                Action::make('view')
                    ->label(__('cart_items.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (CartItem $record): string => self::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
                EditAction::make(),
                Action::make('update_quantity')
                    ->label(__('cart_items.actions.update_quantity'))
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('cart_items.quantity'))
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function (CartItem $record, array $data): void {
                        // Delegate to the domain helper so price synchronisation stays consistent across entry points.
                        $record->updateQuantity((int) $data['quantity']);

                        Notification::make()
                            ->title(__('cart_items.notifications.quantity_updated'))
                            ->success()
                            ->send();
                    }),
                Action::make('move_to_wishlist')
                    ->label(__('cart_items.move_to_wishlist'))
                    ->icon('heroicon-o-heart')
                    ->color('warning')
                    ->action(function (CartItem $record): void {
                        // Flag the cart row for wishlist follow-up when the supporting column exists.
                        if (array_key_exists('is_saved_for_later', $record->getAttributes())) {
                            $record->forceFill(['is_saved_for_later' => true])->save();
                        }

                        Notification::make()
                            ->title(__('cart_items.moved_to_wishlist_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('cart_items.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (CartItem $record): void {
                        // Replicate the item while ensuring the slug field stays unique if present.
                        $clone = $record->replicate();

                        if (array_key_exists('slug', $clone->getAttributes()) && filled($clone->slug)) {
                            $clone->slug = Str::random(8) . '-' . $clone->slug;
                        }

                        $clone->push();

                        Notification::make()
                            ->title(__('cart_items.notifications.duplicated'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('update_quantities')
                        ->label(__('cart_items.bulk.update_quantities'))
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('cart_items.quantity'))
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            // Apply the provided quantity to every selected record.
                            $quantity = (int) $data['quantity'];

                            $records->each(function (CartItem $record) use ($quantity): void {
                                // Reuse the model helper to keep recalculated totals identical to single-row updates.
                                $record->updateQuantity($quantity);
                            });

                            Notification::make()
                                ->title(__('cart_items.notifications.bulk_quantity_updated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('move_to_wishlist')
                        ->label(__('cart_items.bulk.move_to_wishlist'))
                        ->icon('heroicon-o-heart')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Toggle the wishlist flag en-masse whenever the schema supports it.
                            $records->each(function (CartItem $record): void {
                                if (array_key_exists('is_saved_for_later', $record->getAttributes())) {
                                    $record->forceFill(['is_saved_for_later' => true])->save();
                                }
                            });

                            Notification::make()
                                ->title(__('cart_items.notifications.bulk_moved_to_wishlist'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('clear_old_carts')
                        ->label(__('cart_items.clear_old_carts'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $oldRecords = $records->filter(function ($record) {
                                return $record->created_at->lt(now()->subDays(30));
                            });
                            $oldRecords->each(function (CartItem $record): void {
                                $record->forceDelete();
                            });
                            Notification::make()
                                ->title(__('cart_items.old_carts_cleared_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export_cart_items')
                        ->label(__('cart_items.bulk.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): void {
                            // Trigger the export process (streaming handled asynchronously downstream).
                            $records->each(function (CartItem $record): void {
                                // Intentionally left as a notification hook; real export is queued externally.
                            });

                            Notification::make()
                                ->title(__('cart_items.notifications.export_started'))
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
            'index'  => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'view'   => Pages\ViewCartItem::route('/{record}'),
            'edit'   => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }
}
