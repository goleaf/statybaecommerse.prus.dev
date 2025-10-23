<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Models\OrderItem;
use App\Support\Filament\ProductVariantFieldHelper;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductVariantSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;
use Filament\Schemas\Schema;

/**
 * OrderItemsRelationManager
 *
 * Comprehensive relation manager for Order Items with advanced features:
 * - Product variant selection with search
 * - Quantity and pricing management
 * - Automatic total calculation
 * - Bulk operations
 * - Advanced filtering
 */
final class OrderItemsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'orders.items';

    protected static ?string $modelLabel = 'orders.item';

    protected static ?string $pluralModelLabel = 'orders.items';

    /**
     * Configure the form schema for order items.
     */
    public function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form
            ->schema([
                Section::make(__('orders.item_information'))
                    ->description(__('orders.item_information_description'))
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Grid::make(2)
                            ->components([
                                SearchableInput::make('product_variant_id')
                                    ->label(__('orders.product_variant'))
                                    ->placeholder(__('orders.placeholders.product_variant'))
                                    ->searchUsing(fn (string $term): array => ProductVariantSearch::results($term))
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                    // Refer to docs/filament/searchable-inputs.md for helper usage guidance and payload expectations.
                                    ->afterStateHydrated(fn (SearchableInput $component, ?int $state) => ProductVariantFieldHelper::hydrateSearchableVariant($component, $state))
                                ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                    if ($state === null || $state === '') {
                                        // Reset dependent fields when the variant lookup clears.
                                        SearchableInputHelper::clear($set, [
                                            'product_variant_id' => null,
                                            'product_id'         => null,
                                            'name'               => null,
                                            'sku'                => null,
                                            'unit_price'         => null,
                                            'total'              => 0,
                                        ]);

                                        ProductVariantFieldHelper::handleVariantSelection(null, $set, $get);

                                        return;
                                    }

                                    ProductVariantFieldHelper::handleVariantSelection($state, $set, $get);
                                }),
                                TextInput::make('quantity')
                                    ->label(__('orders.quantity'))
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(static function ($state, callable $set, callable $get): void {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total', $unitPrice * $state);
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('unit_price')
                                    ->label(__('orders.fields.unit_price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(static function ($state, callable $set, callable $get): void {
                                        $quantity = $get('quantity') ?? 1;
                                        $set('total', $state * $quantity);
                                    })
                                    ->prefixIcon('heroicon-o-currency-euro'),
                                TextInput::make('discount_amount')
                                    ->label(__('orders.fields.discount_amount'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(static function ($state, callable $set, callable $get): void {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $quantity = $get('quantity') ?? 1;
                                        $discount = $state ?? 0;
                                        $set('total', ($unitPrice * $quantity) - $discount);
                                    })
                                    ->prefixIcon('heroicon-o-tag'),
                                Placeholder::make('total')
                                    ->label(__('orders.total'))
                                    ->content(static function ($get): string {
                                        $unitPrice = (float) $get('unit_price') ?? 0;
                                        $quantity = (int) $get('quantity') ?? 1;
                                        $discount = (float) $get('discount_amount') ?? 0;

                                        $total = ($unitPrice * $quantity) - $discount;

                                        return '€' . number_format($total, 2);
                                    }),
                            ]),
                        Hidden::make('product_id')
                            ->required(),
                        Hidden::make('name')
                            ->required(),
                        Hidden::make('sku')
                            ->required(),
                        Hidden::make('total')
                            ->default(static function ($get): float {
                                $unitPrice = (float) $get('unit_price') ?? 0;
                                $quantity = (int) $get('quantity') ?? 1;
                                $discount = (float) $get('discount_amount') ?? 0;

                                return ($unitPrice * $quantity) - $discount;
                            }),
                    ])
                    ->collapsible(),
                Section::make(__('orders.additional_details'))
                    ->description(__('orders.additional_details_description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('orders.item_notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('orders.item_notes_help')),
                    ])
                    ->collapsible(),
            ]);
    }

    /**
     * Configure the table for order items.
     */
    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('productVariant.name')
                    ->label(__('orders.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('productVariant.sku')
                    ->label(__('orders.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('quantity')
                    ->label(__('orders.fields.quantity'))
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label(__('orders.fields.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('orders.fields.discount_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
                BadgeColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ? __("orders.item_statuses.{$state}") : '-'),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.fields.status'))
                    ->options([
                        'pending'    => __('orders.item_statuses.pending'),
                        'processing' => __('orders.item_statuses.processing'),
                        'completed'  => __('orders.item_statuses.completed'),
                        'cancelled'  => __('orders.item_statuses.cancelled'),
                    ])
                    ->multiple(),
                TernaryFilter::make('has_discount')
                    ->label(__('orders.filters.has_discount'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('discount_amount', '>', 0),
                        false: fn (Builder $query) => $query->where('discount_amount', '=', 0),
                    ),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit items')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit order items')
                    ->modalWidth('5xl')
                    // Support rapid order item adjustments while keeping financial fields visible.
                    ->configureRepeater(static function (Repeater $repeater): Repeater {
                        return $repeater
                            ->collapsible()
                            ->defaultItems(0)
                            ->schema([
                                Hidden::make('id'),
                                Hidden::make('product_variant_id'),
                                TextInput::make('name')
                                    ->label(__('orders.fields.product'))
                                    ->readOnly()
                                    ->dehydrated(false),
                                TextInput::make('sku')
                                    ->label(__('orders.fields.sku'))
                                    ->readOnly()
                                    ->dehydrated(false),
                                TextInput::make('quantity')
                                    ->label(__('orders.fields.quantity'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label(__('orders.fields.unit_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),
                                TextInput::make('discount_amount')
                                    ->label(__('orders.fields.discount_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0),
                                Textarea::make('notes')
                                    ->label(__('orders.item_notes'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]);
                    }),
                \Filament\Actions\CreateAction::make()
                    ->label(__('orders.add_item'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->color('warning'),
                \Filament\Actions\DeleteAction::make()
                    ->color('danger'),
                Action::make('duplicate_item')
                    ->label(__('orders.duplicate_item'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(static function (OrderItem $record): void {
                        $newItem = $record->replicate();
                        $newItem->save();

                        Notification::make()
                            ->title(__('orders.item_duplicated'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_completed')
                        ->label(__('orders.bulk_mark_completed'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(static function (Collection $records): void {
                            $records->each->update(['status' => 'completed']);

                            Notification::make()
                                ->title(__('orders.bulk_completed_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('apply_discount')
                        ->label(__('orders.bulk_apply_discount'))
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            TextInput::make('discount_amount')
                                ->label(__('orders.discount_amount'))
                                ->numeric()
                                ->required()
                                ->prefix('€')
                                ->step(0.01),
                        ])
                        ->action(static function (Collection $records, array $data): void {
                            $records->each->update(['discount_amount' => $data['discount_amount']]);

                            Notification::make()
                                ->title(__('orders.bulk_discount_applied'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function searchVariants(string $term, int $limit = 15): array
    {
        /** @var Collection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->select(['id', 'name', 'sku', 'price', 'product_id'])
            ->with(['product:id,name,sku'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('sku', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhereHas('product', static function (Builder $productQuery) use ($term): void {
                            $productQuery
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('sku', 'like', "%{$term}%");
                        });
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $variants
            ->map(static function (ProductVariant $variant): SearchResult {
                return SearchResult::make((string) $variant->getKey(), self::formatVariantLabel($variant));
            })
            ->all();
    }

    private static function formatVariantLabel(ProductVariant $variant): string
    {
        $sku = $variant->getAttribute('sku');
        $variantName = $variant->getAttribute('name');
        $productName = $variant->product?->getAttribute('name');

        $parts = [
            sprintf('[%s]', $sku !== null && $sku !== '' ? $sku : '—'),
            (string) ($variantName ?? ''),
        ];

        if ($productName) {
            $parts[] = sprintf('• %s', $productName);
        }

        return trim(implode(' ', array_filter($parts)));
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
