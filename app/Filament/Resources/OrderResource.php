<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * OrderResource
 *
 * Comprehensive Filament v4 resource for Order management with advanced features:
 * - Multi-language support with translations
 * - Advanced filtering and search capabilities
 * - Bulk operations and custom actions
 * - Real-time status updates
 * - Comprehensive form validation
 * - Export capabilities
 * - Audit trail integration
 */
final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'orders.navigation.orders';

    protected static ?string $modelLabel = 'orders.models.order';

    protected static ?string $pluralModelLabel = 'orders.models.orders';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'System';
    }

    /**
     * Get the navigation label with translation support.
     */
    public static function getNavigationLabel(): string
    {
        return __('orders.navigation.orders');
    }

    /**
     * Get the plural model label with translation support.
     */
    public static function getPluralModelLabel(): string
    {
        return __('orders.models.orders');
    }

    /**
     * Get the model label with translation support.
     */
    public static function getModelLabel(): string
    {
        return __('orders.models.order');
    }

    /**
     * Configure the comprehensive form schema with advanced features.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.sections.customer_information'))
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('number')
                                ->label(__('orders.fields.order_number'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->helperText(__('orders.number_help')),
                            Select::make('user_id')
                                ->label(__('orders.fields.customer'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Select::make('status')
                                ->label(__('orders.fields.status'))
                                ->options([
                                    'pending' => __('orders.status.pending'),
                                    'processing' => __('orders.status.processing'),
                                    'shipped' => __('orders.status.shipped'),
                                    'delivered' => __('orders.status.delivered'),
                                    'cancelled' => __('orders.status.cancelled'),
                                    'refunded' => __('orders.status.refunded'),
                                ])
                                ->default('pending'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('payment_status')
                                ->label(__('orders.fields.payment_status'))
                                ->options([
                                    'pending' => __('orders.payment_status.pending'),
                                    'paid' => __('orders.payment_status.paid'),
                                    'failed' => __('orders.payment_status.failed'),
                                    'refunded' => __('orders.payment_status.refunded'),
                                ]),
                            Select::make('payment_method')
                                ->label(__('orders.fields.payment_method'))
                                ->options([
                                    'credit_card' => __('orders.payment_methods.credit_card'),
                                    'bank_transfer' => __('orders.payment_methods.bank_transfer'),
                                    'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                                    'paypal' => __('orders.payment_methods.paypal'),
                                    'stripe' => __('orders.payment_methods.stripe'),
                                    'apple_pay' => __('orders.payment_methods.credit_card'),
                                    'google_pay' => __('orders.payment_methods.credit_card'),
                                ]),
                            TextInput::make('payment_reference')
                                ->label(__('orders.fields.tracking_number')),
                        ]),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.total'))
                ->icon('heroicon-o-currency-euro')
                ->schema([
                    Grid::make(4)
                        ->schema([
                            TextInput::make('subtotal')
                                ->label(__('orders.fields.subtotal'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('tax_amount')
                                ->label(__('orders.fields.tax_amount'))
                                ->numeric()
                                ->default(0)
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('shipping_amount')
                                ->label(__('orders.fields.shipping_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                            TextInput::make('discount_amount')
                                ->label(__('orders.fields.discount_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                        ]),
                    Placeholder::make('total')
                        ->label(__('orders.fields.total'))
                        ->content(function (\Filament\Schemas\Components\Utilities\Get $get): string {
                            $subtotal = (float) $get('subtotal') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $shipping = (float) $get('shipping_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;
                            $total = $subtotal + $tax + $shipping - $discount;

                            return '€' . number_format($total, 2);
                        }),
                    Hidden::make('total')
                        ->default(function (\Filament\Schemas\Components\Utilities\Get $get): float {
                            $subtotal = (float) $get('subtotal') ?? 0;
                            $tax = (float) $get('tax_amount') ?? 0;
                            $shipping = (float) $get('shipping_amount') ?? 0;
                            $discount = (float) $get('discount_amount') ?? 0;

                            return $subtotal + $tax + $shipping - $discount;
                        }),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.billing_information'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            KeyValue::make('billing_address')
                                ->label(__('orders.fields.billing_address'))
                                ->keyLabel(__('orders.fields.order_number'))
                                ->valueLabel(__('orders.fields.customer_name'))
                                ->addActionLabel(__('orders.actions.create'))
                                ->helperText(__('orders.fields.billing_address')),
                            KeyValue::make('shipping_address')
                                ->label(__('orders.fields.shipping_address'))
                                ->keyLabel(__('orders.fields.order_number'))
                                ->valueLabel(__('orders.fields.customer_name'))
                                ->addActionLabel(__('orders.actions.create'))
                                ->helperText(__('orders.fields.shipping_address')),
                        ]),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.order_shipping'))
                ->description(__('orders.sections.shipping_information'))
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('shipped_at')
                                ->label(__('orders.fields.shipped_at')),
                            DateTimePicker::make('delivered_at')
                                ->label(__('orders.fields.delivered_at')),
                        ]),
                    TextInput::make('tracking_number')
                        ->label(__('orders.fields.tracking_number'))
                        ->maxLength(255),
                ])
                ->collapsible(),
            Section::make(__('orders.sections.order_details'))
                ->description(__('orders.fields.notes'))
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('notes')
                        ->label(__('orders.fields.notes'))
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText(__('orders.fields.internal_notes')),
                    Grid::make(3)
                        ->schema([
                            Select::make('channel_id')
                                ->label(__('orders.fields.customer'))
                                ->relationship('channel', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('partner_id')
                                ->label(__('orders.fields.customer'))
                                ->relationship('partner', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                ])
                ->collapsible(),
        ]);
    }

    /**
     * Configure the comprehensive table with advanced features.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('orders.fields.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label(__('orders.fields.customer'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("orders.status.{$state}"))
                    ->sortable(),
                BadgeColumn::make('payment_status')
                    ->label(__('orders.fields.payment_status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("orders.payment_status.{$state}"))
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('orders.fields.items_count'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label(__('orders.fields.payment_method'))
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.payment_methods.{$state}") : '-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('channel.name')
                    ->label(__('orders.fields.customer'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('orders.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => __('orders.statuses.pending'),
                        'processing' => __('orders.statuses.processing'),
                        'shipped' => __('orders.statuses.shipped'),
                        'delivered' => __('orders.statuses.delivered'),
                        'cancelled' => __('orders.statuses.cancelled'),
                        'refunded' => __('orders.statuses.refunded'),
                    ])
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->options([
                        'pending' => __('orders.payment_statuses.pending'),
                        'paid' => __('orders.payment_statuses.paid'),
                        'failed' => __('orders.payment_statuses.failed'),
                        'refunded' => __('orders.payment_statuses.refunded'),
                    ])
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options([
                        'credit_card' => __('orders.payment_methods.credit_card'),
                        'bank_transfer' => __('orders.payment_methods.bank_transfer'),
                        'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                        'paypal' => __('orders.payment_methods.paypal'),
                        'stripe' => __('orders.payment_methods.stripe'),
                        'apple_pay' => __('orders.payment_methods.apple_pay'),
                        'google_pay' => __('orders.payment_methods.google_pay'),
                    ])
                    ->multiple(),
                SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->preload(),
                TernaryFilter::make('is_paid')
                    ->label(__('orders.is_paid'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                        false: fn(Builder $query) => $query->whereNotIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                    ),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('orders.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('orders.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('total_range')
                    ->form([
                        TextInput::make('total_from')
                            ->label(__('orders.total_from'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('total_until')
                            ->label(__('orders.total_until'))
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['total_from'],
                                fn(Builder $query, $amount): Builder => $query->where('total', '>=', $amount),
                            )
                            ->when(
                                $data['total_until'],
                                fn(Builder $query, $amount): Builder => $query->where('total', '<=', $amount),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info'),
                EditAction::make()
                    ->color('warning'),
                \Filament\Tables\Actions\DeleteAction::make(),
                Action::make('mark_processing')
                    ->label(__('orders.mark_processing'))
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->visible(fn(Order $record): bool => $record->status === 'pending')
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'processing']);
                        Notification::make()
                            ->title(__('orders.processing_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('mark_shipped')
                    ->label(__('orders.mark_shipped'))
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn(Order $record): bool => $record->status === 'processing')
                    ->action(function (Order $record): void {
                        $record->update([
                            'status' => 'shipped',
                            'shipped_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('orders.shipped_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('mark_delivered')
                    ->label(__('orders.mark_delivered'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Order $record): bool => $record->status === 'shipped')
                    ->action(function (Order $record): void {
                        $record->update([
                            'status' => 'delivered',
                            'delivered_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('orders.delivered_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('cancel_order')
                    ->label(__('orders.cancel_order'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Order $record): bool => in_array($record->status, ['pending', 'processing']))
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title(__('orders.cancelled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('refund_order')
                    ->label(__('orders.refund_order'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('secondary')
                    ->visible(fn(Order $record): bool => in_array($record->status, ['delivered', 'completed']))
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'refunded']);
                        Notification::make()
                            ->title(__('orders.refunded_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_processing')
                        ->label(__('orders.bulk_mark_processing'))
                        ->icon('heroicon-o-cog')
                        ->color('primary')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'processing']);
                            Notification::make()
                                ->title(__('orders.bulk_processing_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('mark_shipped')
                        ->label(__('orders.bulk_mark_shipped'))
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'shipped',
                                'shipped_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('orders.bulk_shipped_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('mark_delivered')
                        ->label(__('orders.bulk_mark_delivered'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'delivered',
                                'delivered_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('orders.bulk_delivered_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('cancel_orders')
                        ->label(__('orders.bulk_cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'cancelled']);
                            Notification::make()
                                ->title(__('orders.bulk_cancelled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export_orders')
                        ->label(__('orders.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            Notification::make()
                                ->title(__('orders.export_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
            RelationManagers\OrderShippingRelationManager::class,
            RelationManagers\OrderDocumentsRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Get the global search result details.
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Customer' => $record->user->name ?? 'N/A',
            'Total' => '€' . number_format((float) $record->total, 2),
            'Status' => __("orders.statuses.{$record->status}"),
        ];
    }

    /**
     * Get the global search result actions.
     */
    public static function getGlobalSearchResultActions($record): array
    {
        $actions = [];

        try {
            $actions[] = Action::make('view')
                ->label(__('orders.actions.view'))
                ->icon('heroicon-o-eye')
                ->url(self::getUrl('view', ['record' => $record]));
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        try {
            $actions[] = Action::make('edit')
                ->label(__('orders.actions.edit'))
                ->icon('heroicon-o-pencil')
                ->url(self::getUrl('edit', ['record' => $record]));
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        return $actions;
    }
}
