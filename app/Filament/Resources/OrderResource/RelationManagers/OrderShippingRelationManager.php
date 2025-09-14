<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * OrderShippingRelationManager
 * 
 * Filament resource for admin panel management.
 */
class OrderShippingRelationManager extends RelationManager
{
    protected static string $relationship = 'shipping';

    protected static ?string $title = 'orders.shipping_information';

    protected static ?string $modelLabel = 'orders.shipping';

    protected static ?string $pluralModelLabel = 'orders.shipping';

    public function form(Form $form): Form
    {
        return $schema->schema([
                Section::make('orders.shipping_details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('carrier_name')
                                    ->label('orders.carrier_name')
                                    ->maxLength(255),

                                TextInput::make('service')
                                    ->label('orders.service')
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('tracking_number')
                                    ->label('orders.tracking_number')
                                    ->maxLength(255),

                                TextInput::make('tracking_url')
                                    ->label('orders.tracking_url')
                                    ->url()
                                    ->maxLength(255),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('shipped_at')
                                    ->label('orders.shipped_at'),

                                DatePicker::make('estimated_delivery')
                                    ->label('orders.estimated_delivery'),

                                DatePicker::make('delivered_at')
                                    ->label('orders.delivered_at'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('weight')
                                    ->label('orders.weight')
                                    ->numeric()
                                    ->suffix('kg')
                                    ->step(0.001),

                                TextInput::make('cost')
                                    ->label('orders.shipping_cost')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),

                        KeyValue::make('dimensions')
                            ->label('orders.dimensions')
                            ->keyLabel('orders.dimension_type')
                            ->valueLabel('orders.dimension_value')
                            ->addActionLabel('orders.add_dimension'),

                        KeyValue::make('metadata')
                            ->label('orders.metadata')
                            ->keyLabel('orders.metadata_key')
                            ->valueLabel('orders.metadata_value')
                            ->addActionLabel('orders.add_metadata'),

                        Textarea::make('notes')
                            ->label('orders.notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('carrier_name')
            ->columns([
                TextColumn::make('carrier_name')
                    ->label('orders.carrier_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service')
                    ->label('orders.service')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tracking_number')
                    ->label('orders.tracking_number')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('orders.shipping_status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_transit',
                        'success' => 'delivered',
                    ])
                    ->formatStateUsing(fn ($record) => $record->status),

                TextColumn::make('shipped_at')
                    ->label('orders.shipped_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('estimated_delivery')
                    ->label('orders.estimated_delivery')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('delivered_at')
                    ->label('orders.delivered_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('orders.shipping_cost')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
