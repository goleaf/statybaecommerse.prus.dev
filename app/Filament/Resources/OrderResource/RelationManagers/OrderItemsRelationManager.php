<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
final class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'orders.items';

    protected static ?string $modelLabel = 'orders.item';

    protected static ?string $pluralModelLabel = 'orders.items';

    /**
     * Configure the form schema for order items.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('orders.item_information'))
                    ->description(__('orders.item_information_description'))
                    ->icon('heroicon-o-cube')
                    ->components([
                        Grid::make(2)
                            ->components([
                                Select::make('product_variant_id')
                                    ->label(__('orders.product_variant'))
                                    ->relationship('productVariant', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $variant = ProductVariant::find($state);
                                            if ($variant) {
                                                $set('unit_price', $variant->price);
                                                $set('total', $variant->price * ($get('quantity') ?? 1));
                                                $set('product_id', $variant->product_id);
                                                $set('name', $variant->name ?? ($variant->product->name ?? ''));
                                                $set('sku', $variant->sku ?? ($variant->product->sku ?? ''));
                                            }
                                        }
                                    })
                                    ->prefixIcon('heroicon-o-cube'),
                                TextInput::make('quantity')
                                    ->label(__('orders.quantity'))
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total', $unitPrice * $state);
                                    })
                                    ->prefixIcon('heroicon-o-hashtag'),
                            ]),
                        Grid::make(3)
                            ->components([
                                TextInput::make('unit_price')
                                    ->label(__('orders.unit_price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantity = $get('quantity') ?? 1;
                                        $set('total', $state * $quantity);
                                    })
                                    ->prefixIcon('heroicon-o-currency-euro'),
                                TextInput::make('discount_amount')
                                    ->label(__('orders.discount_amount'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $quantity = $get('quantity') ?? 1;
                                        $discount = $state ?? 0;
                                        $set('total', ($unitPrice * $quantity) - $discount);
                                    })
                                    ->prefixIcon('heroicon-o-tag'),
                                Placeholder::make('total')
                                    ->label(__('orders.total'))
                                    ->content(function (Forms\Get $get): string {
                                        $unitPrice = (float) $get('unit_price') ?? 0;
                                        $quantity = (int) $get('quantity') ?? 1;
                                        $discount = (float) $get('discount_amount') ?? 0;

                                        $total = ($unitPrice * $quantity) - $discount;

                                        return '€' . number_format($total, 2);
                                    })
                                    ->prefixIcon('heroicon-o-banknotes'),
                            ]),
                        Hidden::make('product_id')
                            ->required(),
                        Hidden::make('name')
                            ->required(),
                        Hidden::make('sku')
                            ->required(),
                        Hidden::make('total')
                            ->default(function (Forms\Get $get): float {
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
                    ->components([
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
        return $table
            ->columns([
                TextColumn::make('productVariant.name')
                    ->label(__('orders.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
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
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.item_statuses.{$state}") : '-'),
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
                        'pending' => __('orders.item_statuses.pending'),
                        'processing' => __('orders.item_statuses.processing'),
                        'completed' => __('orders.item_statuses.completed'),
                        'cancelled' => __('orders.item_statuses.cancelled'),
                    ])
                    ->multiple(),
                TernaryFilter::make('has_discount')
                    ->label(__('orders.filters.has_discount'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('discount_amount', '>', 0),
                        false: fn(Builder $query) => $query->where('discount_amount', '=', 0),
                    ),
            ])
            ->headerActions([
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
                    ->action(function (OrderItem $record): void {
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
                        ->action(function (Collection $records): void {
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
                        ->action(function (Collection $records, array $data): void {
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
}
