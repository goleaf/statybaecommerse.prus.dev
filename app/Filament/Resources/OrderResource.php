<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * OrderResource
 *
 * Filament v4 resource for Order management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('orders.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('orders.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('orders.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('orders.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('number')
                                ->label(__('orders.number'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Select::make('user_id')
                                ->label(__('orders.customer'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Grid::make(3)
                        ->schema([
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
                                ->default('pending'),
                            Select::make('payment_status')
                                ->label(__('orders.payment_status'))
                                ->options([
                                    'pending' => __('orders.payment_statuses.pending'),
                                    'paid' => __('orders.payment_statuses.paid'),
                                    'failed' => __('orders.payment_statuses.failed'),
                                    'refunded' => __('orders.payment_statuses.refunded'),
                                ])
                                ->required()
                                ->default('pending'),
                            Select::make('payment_method')
                                ->label(__('orders.payment_method'))
                                ->options([
                                    'credit_card' => __('orders.payment_methods.credit_card'),
                                    'bank_transfer' => __('orders.payment_methods.bank_transfer'),
                                    'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                                    'paypal' => __('orders.payment_methods.paypal'),
                                ]),
                        ]),
                ]),
            Section::make(__('orders.pricing'))
                ->schema([
                    Grid::make(4)
                        ->schema([
                            TextInput::make('subtotal')
                                ->label(__('orders.subtotal'))
                                ->numeric()
                                ->prefix('€')
                                ->required(),
                            TextInput::make('tax_amount')
                                ->label(__('orders.tax_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->default(0),
                            TextInput::make('shipping_amount')
                                ->label(__('orders.shipping_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->default(0),
                            TextInput::make('discount_amount')
                                ->label(__('orders.discount_amount'))
                                ->numeric()
                                ->prefix('€')
                                ->default(0),
                        ]),
                    TextInput::make('total')
                        ->label(__('orders.total'))
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->disabled(),
                ]),
            Section::make(__('orders.addresses'))
                ->schema([
                    KeyValue::make('billing_address')
                        ->label(__('orders.billing_address'))
                        ->keyLabel(__('orders.address_field'))
                        ->valueLabel(__('orders.address_value'))
                        ->addActionLabel(__('orders.add_address_field')),
                    KeyValue::make('shipping_address')
                        ->label(__('orders.shipping_address'))
                        ->keyLabel(__('orders.address_field'))
                        ->valueLabel(__('orders.address_value'))
                        ->addActionLabel(__('orders.add_address_field')),
                ]),
            Section::make(__('orders.additional_information'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('orders.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('shipped_at')
                                ->label(__('orders.shipped_at')),
                            DateTimePicker::make('delivered_at')
                                ->label(__('orders.delivered_at')),
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
                TextColumn::make('number')
                    ->label(__('orders.number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('orders.customer'))
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('orders.items_count'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('orders.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ]),
                SelectFilter::make('payment_status')
                    ->label(__('orders.payment_status'))
                    ->options([
                        'pending' => __('orders.payment_statuses.pending'),
                        'paid' => __('orders.payment_statuses.paid'),
                        'failed' => __('orders.payment_statuses.failed'),
                        'refunded' => __('orders.payment_statuses.refunded'),
                    ]),
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
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
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
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_processing')
                        ->label(__('orders.mark_processing'))
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
