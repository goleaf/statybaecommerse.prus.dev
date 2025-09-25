<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderShipping;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * OrderShippingRelationManager
 *
 * Comprehensive relation manager for Order Shipping with advanced features:
 * - Shipping method selection
 * - Tracking information management
 * - Delivery status updates
 * - Cost calculation
 * - Bulk operations
 */
final class OrderShippingRelationManager extends RelationManager
{
    protected static string $relationship = 'shipping';

    protected static ?string $title = 'orders.shipping';

    protected static ?string $modelLabel = 'orders.shipping';

    protected static ?string $pluralModelLabel = 'orders.shipping';

    /**
     * Configure the form schema for order shipping.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('orders.shipping_information'))
                    ->description(__('orders.shipping_information_description'))
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('shipping_method')
                                    ->label(__('orders.shipping_method'))
                                    ->options([
                                        'standard' => __('orders.shipping_methods.standard'),
                                        'express' => __('orders.shipping_methods.express'),
                                        'overnight' => __('orders.shipping_methods.overnight'),
                                        'pickup' => __('orders.shipping_methods.pickup'),
                                        'international' => __('orders.shipping_methods.international'),
                                    ])
                                    ->required(),
                                TextInput::make('tracking_number')
                                    ->label(__('orders.tracking_number'))
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('carrier')
                                    ->label(__('orders.carrier'))
                                    ->maxLength(255),
                                TextInput::make('service_type')
                                    ->label(__('orders.service_type'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.shipping_costs'))
                    ->description(__('orders.shipping_costs_description'))
                    ->icon('heroicon-o-currency-euro')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('base_cost')
                                    ->label(__('orders.base_cost'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('insurance_cost')
                                    ->label(__('orders.insurance_cost'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->default(0),
                                TextInput::make('total_cost')
                                    ->label(__('orders.total_cost'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.delivery_information'))
                    ->description(__('orders.delivery_information_description'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('shipped_at')
                                    ->label(__('orders.shipped_at')),
                                DateTimePicker::make('estimated_delivery')
                                    ->label(__('orders.estimated_delivery')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('delivered_at')
                                    ->label(__('orders.delivered_at')),
                                TextInput::make('delivery_notes')
                                    ->label(__('orders.delivery_notes'))
                                    ->maxLength(500),
                            ]),
                        Toggle::make('is_delivered')
                            ->label(__('orders.is_delivered'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('delivered_at', now());
                                }
                            }),
                    ])
                    ->collapsible(),
                Section::make(__('orders.additional_details'))
                    ->description(__('orders.additional_details_description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('orders.shipping_notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('orders.shipping_notes_help')),
                    ])
                    ->collapsible(),
            ]);
    }

    /**
     * Configure the table for order shipping.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shipping_method')
                    ->label(__('orders.shipping_method'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __("orders.shipping_methods.{$state}") : '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tracking_number')
                    ->label(__('orders.tracking_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('carrier')
                    ->label(__('orders.carrier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label(__('orders.total_cost'))
                    ->money('EUR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                        'secondary' => 'returned',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ? __("orders.shipping_statuses.{$state}") : '-'),
                IconColumn::make('is_delivered')
                    ->label(__('orders.is_delivered'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('shipped_at')
                    ->label(__('orders.shipped_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_delivery')
                    ->label(__('orders.estimated_delivery'))
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
                SelectFilter::make('shipping_method')
                    ->label(__('orders.shipping_method'))
                    ->options([
                        'standard' => __('orders.shipping_methods.standard'),
                        'express' => __('orders.shipping_methods.express'),
                        'overnight' => __('orders.shipping_methods.overnight'),
                        'pickup' => __('orders.shipping_methods.pickup'),
                        'international' => __('orders.shipping_methods.international'),
                    ])
                    ->multiple(),
                SelectFilter::make('status')
                    ->label(__('orders.fields.status'))
                    ->options([
                        'pending' => __('orders.shipping_statuses.pending'),
                        'processing' => __('orders.shipping_statuses.processing'),
                        'shipped' => __('orders.shipping_statuses.shipped'),
                        'delivered' => __('orders.shipping_statuses.delivered'),
                        'cancelled' => __('orders.shipping_statuses.cancelled'),
                        'returned' => __('orders.shipping_statuses.returned'),
                    ])
                    ->multiple(),
                TernaryFilter::make('is_delivered')
                    ->label(__('orders.is_delivered'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_delivered', true),
                        false: fn (Builder $query) => $query->where('is_delivered', false),
                    ),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label(__('orders.add_shipping'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Select::make('shipping_method')
                            ->label(__('orders.shipping_method'))
                            ->options([
                                'standard' => __('orders.shipping_methods.standard'),
                                'express' => __('orders.shipping_methods.express'),
                                'overnight' => __('orders.shipping_methods.overnight'),
                                'pickup' => __('orders.shipping_methods.pickup'),
                                'international' => __('orders.shipping_methods.international'),
                            ])
                            ->required(),
                        TextInput::make('tracking_number')
                            ->label(__('orders.tracking_number'))
                            ->maxLength(255),
                        TextInput::make('carrier')
                            ->label(__('orders.carrier'))
                            ->maxLength(255),
                        TextInput::make('service_type')
                            ->label(__('orders.service_type'))
                            ->maxLength(255),
                        TextInput::make('base_cost')
                            ->label(__('orders.base_cost'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        TextInput::make('insurance_cost')
                            ->label(__('orders.insurance_cost'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->default(0),
                        TextInput::make('total_cost')
                            ->label(__('orders.total_cost'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])
                    ->successNotificationTitle(__('orders.shipped_successfully'))
                    ->using(function (array $data) {
                        $owner = $this->getOwnerRecord();
                        // Ensure totals consistency if not provided
                        if (! isset($data['total_cost'])) {
                            $data['total_cost'] = (float) ($data['base_cost'] ?? 0) + (float) ($data['insurance_cost'] ?? 0);
                        }

                        $record = new OrderShipping;
                        $record->order_id = $owner->getKey();
                        $record->shipping_method = $data['shipping_method'] ?? null;
                        $record->tracking_number = $data['tracking_number'] ?? null;
                        $record->carrier = $data['carrier'] ?? null;
                        $record->service_type = $data['service_type'] ?? null;
                        $record->base_cost = $data['base_cost'] ?? null;
                        $record->insurance_cost = $data['insurance_cost'] ?? null;
                        $record->total_cost = $data['total_cost'] ?? null;
                        $record->status = $data['status'] ?? 'pending';
                        $record->save();

                        return $record;
                    })
                    ->after(function (OrderShipping $record, array $data): void {
                        $record->update([
                            'shipping_method' => $data['shipping_method'] ?? ($record->shipping_method ?? 'express'),
                            'tracking_number' => $data['tracking_number'] ?? $record->tracking_number,
                            'carrier' => $data['carrier'] ?? ($record->carrier ?? 'DHL'),
                            'service_type' => $data['service_type'] ?? ($record->service_type ?? 'Express'),
                            'base_cost' => $data['base_cost'] ?? ($record->base_cost ?? 0),
                            'insurance_cost' => $data['insurance_cost'] ?? ($record->insurance_cost ?? 0),
                            'total_cost' => $data['total_cost'] ?? ($record->base_cost + ($record->insurance_cost ?? 0)),
                        ]);
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->color('warning'),
                \Filament\Actions\DeleteAction::make()
                    ->color('danger'),
                Action::make('mark_shipped')
                    ->label(__('orders.mark_shipped'))
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (OrderShipping $record): bool => $record->status !== 'shipped')
                    ->action(function (OrderShipping $record): void {
                        \Illuminate\Support\Facades\DB::table('order_shippings')
                            ->where('id', $record->getKey())
                            ->update([
                                'status' => 'shipped',
                                'shipped_at' => now(),
                                'updated_at' => now(),
                            ]);

                        Notification::make()
                            ->title(__('orders.shipped_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('mark_delivered')
                    ->label(__('orders.mark_delivered'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (OrderShipping $record): bool => $record->status !== 'delivered')
                    ->action(function (OrderShipping $record): void {
                        \Illuminate\Support\Facades\DB::table('order_shippings')
                            ->where('id', $record->getKey())
                            ->update([
                                'status' => 'delivered',
                                'is_delivered' => true,
                                'delivered_at' => now(),
                                'updated_at' => now(),
                            ]);

                        Notification::make()
                            ->title(__('orders.delivered_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('track_package')
                    ->label(__('orders.track_package'))
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->url(fn (OrderShipping $record): string => $record->tracking_url ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn (OrderShipping $record): bool => ! empty($record->tracking_number)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_shipped')
                        ->label(__('orders.bulk_mark_shipped'))
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            foreach ($records as $rec) {
                                if ($rec instanceof OrderShipping) {
                                    \Illuminate\Support\Facades\DB::table('order_shippings')
                                        ->where('id', $rec->getKey())
                                        ->update([
                                            'status' => 'shipped',
                                            'shipped_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                }
                            }

                            Notification::make()
                                ->title(__('orders.bulk_shipped_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('mark_delivered')
                        ->label(__('orders.bulk_mark_delivered'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'delivered',
                                'is_delivered' => true,
                                'delivered_at' => now(),
                            ]);

                            Notification::make()
                                ->title(__('orders.bulk_delivered_success'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
