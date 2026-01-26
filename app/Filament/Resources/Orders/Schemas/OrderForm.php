<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaGrid::make(3)
                    ->schema([
                        SchemaGrid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                SchemaSection::make(__('orders.sections.order_details'))
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                TextInput::make('number')
                                                    ->label(__('orders.fields.order_number'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->disabled() // Usually auto-generated
                                                    ->dehydrated(),
                                                Select::make('status')
                                                    ->label(__('orders.fields.status'))
                                                    ->options([
                                                        'pending' => __('orders.status.pending'),
                                                        'processing' => __('orders.status.processing'),
                                                        'shipped' => __('orders.status.shipped'),
                                                        'delivered' => __('orders.status.delivered'),
                                                        'cancelled' => __('orders.status.cancelled'),
                                                        'refunded' => __('orders.status.refunded'),
                                                        'returned' => __('orders.status.returned'),
                                                    ])
                                                    ->required()
                                                    ->native(false),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->schema([
                                                TextInput::make('currency')
                                                    ->label('Valiuta') // Missing translation key, defaulting
                                                    ->default('EUR')
                                                    ->required(),
                                                TextInput::make('total')
                                                    ->label(__('orders.fields.total'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->disabled() // Should be calculated
                                                    ->dehydrated(),
                                            ]),
                                        Textarea::make('notes')
                                            ->label(__('orders.fields.notes'))
                                            ->columnSpanFull(),
                                    ]),

                                SchemaSection::make(__('orders.sections.shipping_information'))
                                    ->schema([
                                        SchemaGrid::make(2)
                                            ->schema([
                                                // Assuming shipping_address is JSON/array. 
                                                // For simple editing we might use KeyValue or specific fields if we know structure.
                                                // Using KeyValue for now or simple placeholders as structure is dynamic.
                                                // Actually, let's use a Placeholder for address if it's complex, or simple text inputs if flattened.
                                                // Order model casts it to array.
                                                // Let's use simplified inputs for demo:
                                                TextInput::make('shipping_address.street')
                                                    ->label('Gatvė'),
                                                TextInput::make('shipping_address.city')
                                                    ->label('Miestas'),
                                                TextInput::make('shipping_address.zip')
                                                    ->label('Pašto kodas'),
                                                TextInput::make('shipping_address.country')
                                                    ->label('Šalis'),
                                            ]),
                                    ]),
                            ]),

                        SchemaGrid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                SchemaSection::make(__('orders.sections.customer_information'))
                                    ->schema([
                                        Select::make('user_id')
                                            ->label(__('orders.fields.customer'))
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->required(),
                                                TextInput::make('email')
                                                    ->email()
                                                    ->required(),
                                            ]),
                                        TextInput::make('payment_method')
                                            ->label(__('orders.fields.payment_method')),
                                        TextInput::make('payment_status')
                                            ->label(__('orders.fields.payment_status')),
                                    ]),
                                
                                SchemaSection::make(__('orders.fields.additional_details'))
                                    ->schema([
                                        DateTimePicker::make('created_at')
                                            ->label(__('orders.fields.created_at'))
                                            ->disabled(),
                                        DateTimePicker::make('updated_at')
                                            ->label(__('orders.fields.updated_at'))
                                            ->disabled(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
