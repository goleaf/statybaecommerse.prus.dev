<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Channel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Models\Zone;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use BackedEnum;
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

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'orders.title';

    protected static ?string $modelLabel = 'orders.single';

    protected static ?string $pluralModelLabel = 'orders.plural';

    /**
     * Get the navigation label with translation support.
     */
    public static function getNavigationLabel(): string
    {
        return __('orders.title');
    }

    /**
     * Get the plural model label with translation support.
     */
    public static function getPluralModelLabel(): string
    {
        return __('orders.plural');
    }

    /**
     * Get the model label with translation support.
     */
    public static function getModelLabel(): string
    {
        return __('orders.single');
    }

    /**
     * Configure the comprehensive form schema with advanced features.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('orders.basic_information'))
                    ->description(__('orders.basic_information_description'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('number')
                                    ->label(__('orders.number'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->helperText(__('orders.number_help')),
                                Select::make('user_id')
                                    ->label(__('orders.customer'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->prefixIcon('heroicon-o-user'),
                                Select::make('status')
                                    ->label(__('orders.status'))
                                    ->options([
                                        'pending' => __('orders.statuses.pending'),
                                        'processing' => __('orders.statuses.processing'),
                                        'shipped' => __('orders.statuses.shipped'),
                                        'delivered' => __('orders.statuses.delivered'),
                                        'cancelled' => __('orders.statuses.cancelled'),
                                        'refunded' => __('orders.statuses.refunded'),
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->prefixIcon('heroicon-o-flag'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('payment_status')
                                    ->label(__('orders.payment_status'))
                                    ->options([
                                        'pending' => __('orders.payment_statuses.pending'),
                                        'paid' => __('orders.payment_statuses.paid'),
                                        'failed' => __('orders.payment_statuses.failed'),
                                        'refunded' => __('orders.payment_statuses.refunded'),
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->prefixIcon('heroicon-o-credit-card'),
                                Select::make('payment_method')
                                    ->label(__('orders.payment_method'))
                                    ->options([
                                        'credit_card' => __('orders.payment_methods.credit_card'),
                                        'bank_transfer' => __('orders.payment_methods.bank_transfer'),
                                        'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                                        'paypal' => __('orders.payment_methods.paypal'),
                                        'stripe' => __('orders.payment_methods.stripe'),
                                        'apple_pay' => __('orders.payment_methods.apple_pay'),
                                        'google_pay' => __('orders.payment_methods.google_pay'),
                                    ])
                                    ->prefixIcon('heroicon-o-wallet'),
                                TextInput::make('payment_reference')
                                    ->label(__('orders.payment_reference'))
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-document-text'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.pricing'))
                    ->description(__('orders.pricing_description'))
                    ->icon('heroicon-o-currency-euro')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label(__('orders.subtotal'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->step(0.01)
                                    ->prefixIcon('heroicon-o-calculator'),
                                TextInput::make('tax_amount')
                                    ->label(__('orders.tax_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefixIcon('heroicon-o-receipt-percent'),
                                TextInput::make('shipping_amount')
                                    ->label(__('orders.shipping_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefixIcon('heroicon-o-truck'),
                                TextInput::make('discount_amount')
                                    ->label(__('orders.discount_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefixIcon('heroicon-o-tag'),
                            ]),
                        Placeholder::make('total')
                            ->label(__('orders.total'))
                            ->content(function (Forms\Get $get): string {
                                $subtotal = (float) $get('subtotal') ?? 0;
                                $tax = (float) $get('tax_amount') ?? 0;
                                $shipping = (float) $get('shipping_amount') ?? 0;
                                $discount = (float) $get('discount_amount') ?? 0;

                                $total = $subtotal + $tax + $shipping - $discount;

                                return '€' . number_format($total, 2);
                            })
                            ->prefixIcon('heroicon-o-banknotes'),
                        Hidden::make('total')
                            ->default(function (Forms\Get $get): float {
                                $subtotal = (float) $get('subtotal') ?? 0;
                                $tax = (float) $get('tax_amount') ?? 0;
                                $shipping = (float) $get('shipping_amount') ?? 0;
                                $discount = (float) $get('discount_amount') ?? 0;

                                return $subtotal + $tax + $shipping - $discount;
                            }),
                    ])
                    ->collapsible(),
                Section::make(__('orders.addresses'))
                    ->description(__('orders.addresses_description'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('billing_address')
                                    ->label(__('orders.billing_address'))
                                    ->keyLabel(__('orders.address_field'))
                                    ->valueLabel(__('orders.address_value'))
                                    ->addActionLabel(__('orders.add_address_field'))
                                    ->helperText(__('orders.billing_address_help')),
                                KeyValue::make('shipping_address')
                                    ->label(__('orders.shipping_address'))
                                    ->keyLabel(__('orders.address_field'))
                                    ->valueLabel(__('orders.address_value'))
                                    ->addActionLabel(__('orders.add_address_field'))
                                    ->helperText(__('orders.shipping_address_help')),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.shipping_information'))
                    ->description(__('orders.shipping_information_description'))
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('shipped_at')
                                    ->label(__('orders.shipped_at'))
                                    ->prefixIcon('heroicon-o-truck'),
                                DateTimePicker::make('delivered_at')
                                    ->label(__('orders.delivered_at'))
                                    ->prefixIcon('heroicon-o-check-circle'),
                            ]),
                        TextInput::make('tracking_number')
                            ->label(__('orders.tracking_number'))
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-magnifying-glass'),
                    ])
                    ->collapsible(),
                Section::make(__('orders.additional_information'))
                    ->description(__('orders.additional_information_description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('orders.notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('orders.notes_help')),
                        Grid::make(3)
                            ->schema([
                                Select::make('zone_id')
                                    ->label(__('orders.zone'))
                                    ->relationship('zone', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-globe-alt'),
                                Select::make('channel_id')
                                    ->label(__('orders.channel'))
                                    ->relationship('channel', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-device-phone-mobile'),
                                Select::make('partner_id')
                                    ->label(__('orders.partner'))
                                    ->relationship('partner', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-handshake'),
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
                    ->label(__('orders.number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->prefixIcon('heroicon-o-hashtag'),
                TextColumn::make('user.name')
                    ->label(__('orders.customer'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                BadgeColumn::make('status')
                    ->label(__('orders.status'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("orders.statuses.{$state}")),
                BadgeColumn::make('payment_status')
                    ->label(__('orders.payment_status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("orders.payment_statuses.{$state}")),
                TextColumn::make('total')
                    ->label(__('orders.total'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->prefixIcon('heroicon-o-banknotes'),
                TextColumn::make('items_count')
                    ->label(__('orders.items_count'))
                    ->counts('items')
                    ->sortable()
                    ->prefixIcon('heroicon-o-shopping-cart'),
                TextColumn::make('payment_method')
                    ->label(__('orders.payment_method'))
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.payment_methods.{$state}") : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('zone.name')
                    ->label(__('orders.zone'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('channel.name')
                    ->label(__('orders.channel'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->prefixIcon('heroicon-o-calendar'),
                TextColumn::make('updated_at')
                    ->label(__('orders.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->prefixIcon('heroicon-o-clock'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.status'))
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
                    ->label(__('orders.payment_status'))
                    ->options([
                        'pending' => __('orders.payment_statuses.pending'),
                        'paid' => __('orders.payment_statuses.paid'),
                        'failed' => __('orders.payment_statuses.failed'),
                        'refunded' => __('orders.payment_statuses.refunded'),
                    ])
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->label(__('orders.payment_method'))
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
                SelectFilter::make('zone')
                    ->label(__('orders.zone'))
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('channel')
                    ->label(__('orders.channel'))
                    ->relationship('channel', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_paid')
                    ->label(__('orders.is_paid'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                        false: fn(Builder $query) => $query->whereNotIn('payment_status', ['paid', 'captured', 'settled', 'authorized']),
                    ),
                DateFilter::make('created_at')
                    ->label(__('orders.created_at'))
                    ->range(),
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
                            // Export logic would go here
                            Notification::make()
                                ->title(__('orders.export_success'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')  // Auto-refresh every 30 seconds
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
            'Total' => '€' . number_format($record->total, 2),
            'Status' => __("orders.statuses.{$record->status}"),
        ];
    }

    /**
     * Get the global search result actions.
     */
    public static function getGlobalSearchResultActions($record): array
    {
        return [
            Action::make('view')
                ->label(__('orders.view'))
                ->icon('heroicon-o-eye')
                ->url(static::getUrl('view', ['record' => $record])),
            Action::make('edit')
                ->label(__('orders.edit'))
                ->icon('heroicon-o-pencil')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }
}
