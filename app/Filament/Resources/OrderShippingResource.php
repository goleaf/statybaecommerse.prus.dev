<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\OrderShippingResource\Pages;
use App\Models\Order;
use App\Models\OrderShipping;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use UnitEnum;

final class OrderShippingResource extends Resource
{
    protected static ?string $model = OrderShipping::class;
    
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-truck';
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Orders;
    
    protected static ?int $navigationSort = 3;
    
    public static function getNavigationLabel(): string
    {
        return __('admin.order_shippings.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.order_shippings.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.order_shippings.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.order_shippings.basic_information'))
                    ->description(__('admin.order_shippings.basic_information_description'))
                    ->schema([
                        Select::make('order_id')
                            ->label(__('admin.order_shippings.order'))
                            ->options(Order::pluck('order_number', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('carrier_name')
                                    ->label(__('admin.order_shippings.carrier_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('service')
                                    ->label(__('admin.order_shippings.service'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('tracking_number')
                                    ->label(__('admin.order_shippings.tracking_number'))
                                    ->maxLength(255),

                                TextInput::make('tracking_url')
                                    ->label(__('admin.order_shippings.tracking_url'))
                                    ->url()
                                    ->maxLength(500),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('shipped_at')
                                    ->label(__('admin.order_shippings.shipped_at')),

                                DateTimePicker::make('estimated_delivery')
                                    ->label(__('admin.order_shippings.estimated_delivery')),

                                DateTimePicker::make('delivered_at')
                                    ->label(__('admin.order_shippings.delivered_at')),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('weight')
                                    ->label(__('admin.order_shippings.weight'))
                                    ->numeric()
                                    ->step(0.001)
                                    ->suffix('kg'),

                                TextInput::make('cost')
                                    ->label(__('admin.order_shippings.cost'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),

                                TextInput::make('dimensions')
                                    ->label(__('admin.order_shippings.dimensions'))
                                    ->helperText('Format: L x W x H (cm)'),
                            ]),

                        KeyValue::make('metadata')
                            ->label(__('admin.order_shippings.metadata'))
                            ->keyLabel(__('admin.order_shippings.metadata_key'))
                            ->valueLabel(__('admin.order_shippings.metadata_value'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label(__('admin.order_shippings.order'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.orders.view', $record->order_id)),

                TextColumn::make('carrier_name')
                    ->label(__('admin.order_shippings.carrier_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service')
                    ->label(__('admin.order_shippings.service'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tracking_number')
                    ->label(__('admin.order_shippings.tracking_number'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->label(__('admin.order_shippings.status'))
                    ->getStateUsing(function ($record) {
                        if ($record->delivered_at) return 'delivered';
                        if ($record->shipped_at) return 'shipped';
                        return 'pending';
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'shipped',
                        'success' => 'delivered',
                    ]),

                TextColumn::make('shipped_at')
                    ->label(__('admin.order_shippings.shipped_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estimated_delivery')
                    ->label(__('admin.order_shippings.estimated_delivery'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivered_at')
                    ->label(__('admin.order_shippings.delivered_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cost')
                    ->label(__('admin.order_shippings.cost'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label(__('admin.order_shippings.order'))
                    ->options(Order::pluck('order_number', 'id'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('carrier_name')
                    ->label(__('admin.order_shippings.carrier_name'))
                    ->options(fn () => OrderShipping::distinct()->pluck('carrier_name', 'carrier_name'))
                    ->searchable(),

                Filter::make('shipped_at')
                    ->label(__('admin.order_shippings.shipped_at'))
                    ->form([
                        DateTimePicker::make('shipped_from')
                            ->label(__('admin.order_shippings.shipped_from')),
                        DateTimePicker::make('shipped_until')
                            ->label(__('admin.order_shippings.shipped_until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['shipped_from'],
                                fn($query, $date) => $query->whereDate('shipped_at', '>=', $date),
                            )
                            ->when(
                                $data['shipped_until'],
                                fn($query, $date) => $query->whereDate('shipped_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_shipped')
                        ->label(__('admin.order_shippings.mark_shipped'))
                        ->icon('heroicon-o-truck')
                        ->action(function ($records) {
                            $records->each->update(['shipped_at' => now()]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('mark_delivered')
                        ->label(__('admin.order_shippings.mark_delivered'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->update(['delivered_at' => now()]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderShippings::route('/'),
            'create' => Pages\CreateOrderShipping::route('/create'),
            'view' => Pages\ViewOrderShipping::route('/{record}'),
            'edit' => Pages\EditOrderShipping::route('/{record}/edit'),
        ];
    }
}
