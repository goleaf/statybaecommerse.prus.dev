<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('orders.basic_information'))
                    ->schema([
                        TextInput::make('order_number')
                            ->label(__('orders.order_number'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Order::class, 'order_number', ignoreRecord: true),
                        Select::make('status')
                            ->label(__('orders.status'))
                            ->options(OrderStatus::getOptions())
                            ->required()
                            ->default(OrderStatus::PENDING->value),
                        TextInput::make('total_amount')
                            ->label(__('orders.total_amount'))
                            ->numeric()
                            ->required()
                            ->prefix('€'),
                        TextInput::make('currency')
                            ->label(__('orders.currency'))
                            ->default('EUR')
                            ->maxLength(3),
                    ]),
                Section::make(__('orders.shipping_information'))
                    ->schema([
                        TextInput::make('shipping_address')
                            ->label(__('orders.shipping_address'))
                            ->maxLength(500),
                        TextInput::make('billing_address')
                            ->label(__('orders.billing_address'))
                            ->maxLength(500),
                        TextInput::make('tracking_number')
                            ->label(__('orders.tracking_number'))
                            ->maxLength(255),
                        DateTimePicker::make('shipped_at')
                            ->label(__('orders.shipped_at')),
                        DateTimePicker::make('delivered_at')
                            ->label(__('orders.delivered_at')),
                    ]),
                Section::make(__('orders.additional_information'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('orders.notes'))
                            ->maxLength(1000)
                            ->rows(3),
                        TextInput::make('payment_method')
                            ->label(__('orders.payment_method'))
                            ->maxLength(255),
                        TextInput::make('shipping_method')
                            ->label(__('orders.shipping_method'))
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make(__('orders.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('orders.id')),
                                TextEntry::make('order_number')
                                    ->label(__('orders.order_number')),
                                TextEntry::make('status')
                                    ->label(__('orders.status'))
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        'refunded' => 'secondary',
                                        'returned' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('total_amount')
                                    ->label(__('orders.total_amount'))
                                    ->money('EUR'),
                            ]),
                    ]),
                InfolistSection::make(__('orders.shipping_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('shipping_address')
                                    ->label(__('orders.shipping_address')),
                                TextEntry::make('billing_address')
                                    ->label(__('orders.billing_address')),
                                TextEntry::make('tracking_number')
                                    ->label(__('orders.tracking_number')),
                                TextEntry::make('shipped_at')
                                    ->label(__('orders.shipped_at'))
                                    ->dateTime(),
                                TextEntry::make('delivered_at')
                                    ->label(__('orders.delivered_at'))
                                    ->dateTime(),
                            ]),
                    ]),
                InfolistSection::make(__('orders.additional_information'))
                    ->schema([
                        TextEntry::make('notes')
                            ->label(__('orders.notes')),
                        TextEntry::make('payment_method')
                            ->label(__('orders.payment_method')),
                        TextEntry::make('shipping_method')
                            ->label(__('orders.shipping_method')),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('id')
                    ->label(__('orders.id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('order_number')
                    ->label(__('orders.order_number'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                BadgeColumn::make('status')
                    ->label(__('orders.status'))
                    ->colors([
                        'warning' => fn($state): bool => $state === 'pending',
                        'info' => fn($state): bool => $state === 'confirmed',
                        'primary' => fn($state): bool => $state === 'processing',
                        'success' => fn($state): bool => in_array($state, ['shipped', 'delivered']),
                        'danger' => fn($state): bool => $state === 'cancelled',
                        'secondary' => fn($state): bool => $state === 'refunded',
                        'warning' => fn($state): bool => $state === 'returned',
                    ]),
                TextColumn::make('total_amount')
                    ->label(__('orders.total_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('orders.currency'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_method')
                    ->label(__('orders.payment_method'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_method')
                    ->label(__('orders.shipping_method'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tracking_number')
                    ->label(__('orders.tracking_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_paid')
                    ->label(__('orders.is_paid'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipped_at')
                    ->label(__('orders.shipped_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivered_at')
                    ->label(__('orders.delivered_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.status'))
                    ->options(OrderStatus::getOptions()),
                SelectFilter::make('payment_method')
                    ->label(__('orders.payment_method'))
                    ->options([
                        'credit_card' => __('orders.payment_methods.credit_card'),
                        'bank_transfer' => __('orders.payment_methods.bank_transfer'),
                        'paypal' => __('orders.payment_methods.paypal'),
                        'cash_on_delivery' => __('orders.payment_methods.cash_on_delivery'),
                    ]),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('orders.created_from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
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
            ->headerActions([
                CreateAction::make()
                    ->label(__('orders.create_order')),
                AssociateAction::make()
                    ->label(__('orders.associate_order')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
