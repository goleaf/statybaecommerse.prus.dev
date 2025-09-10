<?php

namespace App\Filament\Resources;

use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.orders');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Section::make(__('admin.order_information'))
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()
                            ->unique(Order::class, 'number', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => __('translations.pending'),
                                'processing' => __('translations.processing'),
                                'shipped' => __('translations.shipped'),
                                'delivered' => __('translations.delivered'),
                                'cancelled' => __('translations.cancelled'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('currency')
                            ->default('EUR')
                            ->maxLength(3),
                    ])
                    ->columns(2),
                Schemas\Section::make(__('admin.pricing'))
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('shipping_amount')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('discount_amount')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])
                    ->columns(2),
                Schemas\Section::make(__('admin.addresses_information'))
                    ->schema([
                        Forms\Components\KeyValue::make('billing_address')
                            ->label(__('admin.billing_address')),
                        Forms\Components\KeyValue::make('shipping_address')
                            ->label(__('admin.shipping_address')),
                    ])
                    ->columns(2),
                Schemas\Section::make(__('admin.dates_information'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label(__('admin.shipped_at')),
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label(__('admin.delivered_at')),
                    ])
                    ->columns(2),
                Schemas\Section::make(__('admin.notes_information'))
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.orders.fields.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.customer'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('admin.items')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.order_date'))
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipped_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('translations.pending'),
                        'processing' => __('translations.processing'),
                        'shipped' => __('translations.shipped'),
                        'delivered' => __('translations.delivered'),
                        'cancelled' => __('translations.cancelled'),
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.order_created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.order_created_until')),
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
            ->recordActions([
                DocumentAction::make()
                    ->label(__('admin.documents.generate'))
                    ->variables(fn(Order $record) => [
                        '$ORDER_NUMBER' => $record->number,
                        '$ORDER_DATE' => $record->created_at->format('Y-m-d'),
                        '$ORDER_TOTAL' => number_format($record->total, 2) . ' ' . $record->currency,
                        '$ORDER_SUBTOTAL' => number_format($record->subtotal, 2) . ' ' . $record->currency,
                        '$ORDER_TAX' => number_format($record->tax_amount, 2) . ' ' . $record->currency,
                        '$ORDER_SHIPPING' => number_format($record->shipping_amount, 2) . ' ' . $record->currency,
                        '$ORDER_DISCOUNT' => number_format($record->discount_amount, 2) . ' ' . $record->currency,
                        '$ORDER_STATUS' => ucfirst($record->status),
                        '$CUSTOMER_NAME' => $record->user?->name ?? 'Guest',
                        '$CUSTOMER_EMAIL' => $record->user?->email ?? '',
                        '$BILLING_ADDRESS' => is_array($record->billing_address) ? implode(', ', $record->billing_address) : $record->billing_address,
                        '$SHIPPING_ADDRESS' => is_array($record->shipping_address) ? implode(', ', $record->shipping_address) : $record->shipping_address,
                    ]),
                ViewAction::make()->label(__('admin.actions.view')),
                EditAction::make()->label(__('admin.actions.edit')),
                DeleteAction::make()->label(__('admin.actions.delete')),
                Action::make('restore')
                    ->label(__('admin.actions.restore'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(?Order $record): bool => true)
                    ->action(function (?Order $record): void {
                        if ($record && method_exists($record, 'restore')) {
                            $record->restore();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ItemsRelationManager::class, // Needs further API fixes
            // RelationManagers\DocumentsRelationManager::class, // Needs creation
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

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'user.name', 'user.email', 'status', 'notes'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Customer' => $record->user?->name,
            'Status' => ucfirst($record->status),
            'Total' => '€' . number_format($record->total, 2),
            'Date' => $record->created_at->format('Y-m-d'),
        ];
    }
}
