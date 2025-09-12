<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Channel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Models\Zone;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'orders.navigation_label';

    protected static ?string $modelLabel = 'orders.model_label';

    protected static ?string $pluralModelLabel = 'orders.plural_model_label';

    /**
     * @var string|\BackedEnum|null
     */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'orders.navigation_group';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('orders.basic_information')
                    ->schema([
                        FormGrid::make(2)
                            ->schema([
                                TextInput::make('number')
                                    ->label('orders.number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Select::make('user_id')
                                    ->label('orders.customer')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('users.name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label('users.email')
                                            ->email()
                                            ->required()
                                            ->unique()
                                            ->maxLength(255),
                                    ]),
                                Select::make('status')
                                    ->label('orders.status')
                                    ->options([
                                        'pending' => 'orders.statuses.pending',
                                        'processing' => 'orders.statuses.processing',
                                        'confirmed' => 'orders.statuses.confirmed',
                                        'shipped' => 'orders.statuses.shipped',
                                        'delivered' => 'orders.statuses.delivered',
                                        'completed' => 'orders.statuses.completed',
                                        'cancelled' => 'orders.statuses.cancelled',
                                    ])
                                    ->required()
                                    ->default('pending'),
                                Select::make('payment_status')
                                    ->label('orders.payment_status')
                                    ->options([
                                        'pending' => 'orders.payment_statuses.pending',
                                        'paid' => 'orders.payment_statuses.paid',
                                        'failed' => 'orders.payment_statuses.failed',
                                        'refunded' => 'orders.payment_statuses.refunded',
                                        'partially_refunded' => 'orders.payment_statuses.partially_refunded',
                                    ])
                                    ->default('pending'),
                                TextInput::make('payment_method')
                                    ->label('orders.payment_method')
                                    ->maxLength(255),
                                TextInput::make('payment_reference')
                                    ->label('orders.payment_reference')
                                    ->maxLength(255),
                            ]),
                    ]),
                FormSection::make('orders.financial_information')
                    ->schema([
                        FormGrid::make(4)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('orders.subtotal')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('tax_amount')
                                    ->label('orders.tax_amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('shipping_amount')
                                    ->label('orders.shipping_amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('discount_amount')
                                    ->label('orders.discount_amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('total')
                                    ->label('orders.total')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->required(),
                                TextInput::make('currency')
                                    ->label('orders.currency')
                                    ->default('EUR')
                                    ->maxLength(3)
                                    ->required(),
                            ]),
                    ]),
                FormSection::make('orders.addresses')
                    ->schema([
                        FormGrid::make(2)
                            ->schema([
                                KeyValue::make('billing_address')
                                    ->label('orders.billing_address')
                                    ->keyLabel('orders.address_field')
                                    ->valueLabel('orders.address_value')
                                    ->addActionLabel('orders.add_address_field'),
                                KeyValue::make('shipping_address')
                                    ->label('orders.shipping_address')
                                    ->keyLabel('orders.address_field')
                                    ->valueLabel('orders.address_value')
                                    ->addActionLabel('orders.add_address_field'),
                            ]),
                    ]),
                FormSection::make('orders.additional_information')
                    ->schema([
                        FormGrid::make(3)
                            ->schema([
                                Select::make('channel_id')
                                    ->label('orders.channel')
                                    ->relationship('channel', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('zone_id')
                                    ->label('orders.zone')
                                    ->relationship('zone', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('partner_id')
                                    ->label('orders.partner')
                                    ->relationship('partner', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Textarea::make('notes')
                            ->label('orders.notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('shipped_at')
                                    ->label('orders.shipped_at'),
                                DatePicker::make('delivered_at')
                                    ->label('orders.delivered_at'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('orders.number')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label('orders.customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                BadgeColumn::make('status')
                    ->label('orders.status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => ['confirmed', 'shipped', 'delivered', 'completed'],
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => __("orders.statuses.{$state}")),
                BadgeColumn::make('payment_status')
                    ->label('orders.payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => ['failed', 'refunded'],
                        'info' => 'partially_refunded',
                    ])
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.payment_statuses.{$state}") : '-')
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('orders.total')
                    ->money('EUR')
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('items_count')
                    ->label('orders.items_count')
                    ->counts('items')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('channel.name')
                    ->label('orders.channel')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('zone.name')
                    ->label('orders.zone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('partner.name')
                    ->label('orders.partner')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('orders.created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipped_at')
                    ->label('orders.shipped_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivered_at')
                    ->label('orders.delivered_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('orders.status')
                    ->options([
                        'pending' => 'orders.statuses.pending',
                        'processing' => 'orders.statuses.processing',
                        'confirmed' => 'orders.statuses.confirmed',
                        'shipped' => 'orders.statuses.shipped',
                        'delivered' => 'orders.statuses.delivered',
                        'completed' => 'orders.statuses.completed',
                        'cancelled' => 'orders.statuses.cancelled',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('orders.payment_status')
                    ->options([
                        'pending' => 'orders.payment_statuses.pending',
                        'paid' => 'orders.payment_statuses.paid',
                        'failed' => 'orders.payment_statuses.failed',
                        'refunded' => 'orders.payment_statuses.refunded',
                        'partially_refunded' => 'orders.payment_statuses.partially_refunded',
                    ]),
                SelectFilter::make('channel_id')
                    ->label('orders.channel')
                    ->relationship('channel', 'name'),
                SelectFilter::make('zone_id')
                    ->label('orders.zone')
                    ->relationship('zone', 'name'),
                SelectFilter::make('partner_id')
                    ->label('orders.partner')
                    ->relationship('partner', 'name'),
                Filter::make('created_at')
                    ->label('orders.created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('orders.created_from'),
                        DatePicker::make('created_until')
                            ->label('orders.created_until'),
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
                TernaryFilter::make('is_paid')
                    ->label('orders.is_paid')
                    ->queries(
                        true: fn(Builder $query) => $query
                            ->whereIn('payment_status', ['paid', 'captured', 'settled', 'authorized'])
                            ->orWhereIn('status', ['processing', 'confirmed', 'shipped', 'delivered', 'completed']),
                        false: fn(Builder $query) => $query
                            ->where('payment_status', 'pending')
                            ->where('status', 'pending'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    TableViewAction::make(),
                    TableEditAction::make(),
                    TableAction::make('mark_shipped')
                        ->label('orders.actions.mark_shipped')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->visible(fn(Order $record): bool => $record->isShippable())
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            $record->update([
                                'status' => 'shipped',
                                'shipped_at' => now(),
                            ]);
                        }),
                    TableAction::make('mark_delivered')
                        ->label('orders.actions.mark_delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(Order $record): bool => $record->status === 'shipped')
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            $record->update([
                                'status' => 'delivered',
                                'delivered_at' => now(),
                            ]);
                        }),
                    TableAction::make('cancel')
                        ->label('orders.actions.cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(Order $record): bool => $record->canBeCancelled())
                        ->requiresConfirmation()
                        ->action(function (Order $record): void {
                            $record->update(['status' => 'cancelled']);
                        }),
                ]),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableDeleteBulkAction::make(),
                    TableAction::make('bulk_mark_shipped')
                        ->label('orders.actions.bulk_mark_shipped')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(function (Order $record) {
                                if ($record->isShippable()) {
                                    $record->update([
                                        'status' => 'shipped',
                                        'shipped_at' => now(),
                                    ]);
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('orders.basic_information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('number')
                                    ->label('orders.number')
                                    ->weight(FontWeight::Bold)
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->label('orders.status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'confirmed', 'shipped', 'delivered', 'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => __("orders.statuses.{$state}")),
                                TextEntry::make('payment_status')
                                    ->label('orders.payment_status')
                                    ->badge()
                                    ->color(fn(?string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed', 'refunded' => 'danger',
                                        'partially_refunded' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.payment_statuses.{$state}") : '-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('orders.customer')
                                    ->url(fn(?string $state, Order $record): ?string => $record->user ? route('filament.admin.resources.users.view', $record->user) : null),
                                TextEntry::make('created_at')
                                    ->label('orders.created_at')
                                    ->dateTime(),
                            ]),
                    ]),
                Section::make('orders.financial_information')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('orders.subtotal')
                                    ->money('EUR'),
                                TextEntry::make('tax_amount')
                                    ->label('orders.tax_amount')
                                    ->money('EUR'),
                                TextEntry::make('shipping_amount')
                                    ->label('orders.shipping_amount')
                                    ->money('EUR'),
                                TextEntry::make('discount_amount')
                                    ->label('orders.discount_amount')
                                    ->money('EUR'),
                                TextEntry::make('total')
                                    ->label('orders.total')
                                    ->money('EUR')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),
                Section::make('orders.order_items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('orders.product_name'),
                                        TextEntry::make('sku')
                                            ->label('orders.sku'),
                                        TextEntry::make('quantity')
                                            ->label('orders.quantity'),
                                        TextEntry::make('total')
                                            ->label('orders.total')
                                            ->money('EUR'),
                                    ]),
                            ])
                            ->columns(1),
                    ]),
                Section::make('orders.addresses')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValueEntry::make('billing_address')
                                    ->label('orders.billing_address'),
                                KeyValueEntry::make('shipping_address')
                                    ->label('orders.shipping_address'),
                            ]),
                    ]),
                Section::make('orders.additional_information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('channel.name')
                                    ->label('orders.channel'),
                                TextEntry::make('zone.name')
                                    ->label('orders.zone'),
                                TextEntry::make('partner.name')
                                    ->label('orders.partner'),
                            ]),
                        TextEntry::make('notes')
                            ->label('orders.notes')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('shipped_at')
                                    ->label('orders.shipped_at')
                                    ->dateTime(),
                                TextEntry::make('delivered_at')
                                    ->label('orders.delivered_at')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
            RelationManagers\OrderShippingRelationManager::class,
            RelationManagers\OrderDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
