<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Order Details')
                                    ->schema([
                                        TextInput::make('number')
                                            ->required()
                                            ->maxLength(255)
                                            ->disabled()
                                            ->dehydrated(),
                                        Select::make('status')
                                            ->options(OrderStatus::class)
                                            ->required()
                                            ->default(OrderStatus::PENDING),
                                        Select::make('currency')
                                            ->options(['EUR' => 'EUR', 'USD' => 'USD'])
                                            ->default('EUR')
                                            ->required(),
                                    ])->columns(3),

                                Section::make('Shipping Address')
                                    ->schema([
                                        TextInput::make('shipping_address.first_name')->label('First Name'),
                                        TextInput::make('shipping_address.last_name')->label('Last Name'),
                                        TextInput::make('shipping_address.email')->email()->label('Email'),
                                        TextInput::make('shipping_address.phone')->tel()->label('Phone'),
                                        TextInput::make('shipping_address.street')->label('Street')->columnSpanFull(),
                                        TextInput::make('shipping_address.city')->label('City'),
                                        TextInput::make('shipping_address.zip')->label('Zip Code'),
                                        TextInput::make('shipping_address.country')->label('Country'),
                                    ])->columns(2),

                                Section::make('Billing Address')
                                    ->schema([
                                        TextInput::make('billing_address.first_name')->label('First Name'),
                                        TextInput::make('billing_address.last_name')->label('Last Name'),
                                        TextInput::make('billing_address.email')->email()->label('Email'),
                                        TextInput::make('billing_address.phone')->tel()->label('Phone'),
                                        TextInput::make('billing_address.street')->label('Street')->columnSpanFull(),
                                        TextInput::make('billing_address.city')->label('City'),
                                        TextInput::make('billing_address.zip')->label('Zip Code'),
                                        TextInput::make('billing_address.country')->label('Country'),
                                    ])->columns(2)
                                    ->collapsed(),
                            ]),

                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Customer')
                                    ->schema([
                                        Select::make('user_id')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')->required(),
                                                TextInput::make('email')->required()->email(),
                                            ]),
                                    ]),

                                Section::make('Financials')
                                    ->schema([
                                        TextInput::make('subtotal')->numeric()->prefix('€'),
                                        TextInput::make('shipping_amount')->numeric()->prefix('€'),
                                        TextInput::make('tax_amount')->numeric()->prefix('€'),
                                        TextInput::make('discount_amount')->numeric()->prefix('€'),
                                        TextInput::make('total')->numeric()->prefix('€')->required(),
                                    ]),

                                Section::make('Payment')
                                    ->schema([
                                        Select::make('payment_method')
                                            ->options(PaymentMethod::class),
                                        Select::make('payment_status')
                                            ->options(PaymentStatus::class)
                                            ->required(),
                                    ]),
                                
                                Section::make('Dates')
                                    ->schema([
                                        DateTimePicker::make('created_at')->disabled(),
                                        DateTimePicker::make('updated_at')->disabled(),
                                        DateTimePicker::make('shipped_at'),
                                        DateTimePicker::make('delivered_at'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}