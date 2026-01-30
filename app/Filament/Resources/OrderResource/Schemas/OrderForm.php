<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.checkout_order_information'))
                    ->schema([
                        TextInput::make('number')
                            ->label(__('messages.order_number'))
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options(OrderStatus::class)
                            ->required()
                            ->default(OrderStatus::PENDING),
                        Select::make('currency')
                            ->label(__('messages.currency'))
                            ->options(['EUR' => 'EUR', 'USD' => 'USD'])
                            ->default('EUR')
                            ->required(),
                    ])->columns(3),

                Section::make(__('messages.customer'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('messages.customer'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('messages.email'))
                                    ->required()->email(),
                            ]),
                    ]),

                Section::make(__('messages.checkout_shipping_address'))
                    ->schema([
                        TextInput::make('shipping_address.first_name')->label(__('messages.first_name')),
                        TextInput::make('shipping_address.last_name')->label(__('messages.last_name')),
                        TextInput::make('shipping_address.email')->email()->label(__('messages.email')),
                        TextInput::make('shipping_address.phone')->tel()->label(__('messages.phone')),
                        TextInput::make('shipping_address.street')->label(__('messages.street'))->columnSpanFull(),
                        TextInput::make('shipping_address.city')->label(__('messages.city')),
                        TextInput::make('shipping_address.zip')->label(__('messages.zip_code')),
                        TextInput::make('shipping_address.country')->label(__('messages.country')),
                    ])->columns(4),

                Section::make(__('messages.checkout_billing_address'))
                    ->schema([
                        TextInput::make('billing_address.first_name')->label(__('messages.first_name')),
                        TextInput::make('billing_address.last_name')->label(__('messages.last_name')),
                        TextInput::make('billing_address.email')->email()->label(__('messages.email')),
                        TextInput::make('billing_address.phone')->tel()->label(__('messages.phone')),
                        TextInput::make('billing_address.street')->label(__('messages.street'))->columnSpanFull(),
                        TextInput::make('billing_address.city')->label(__('messages.city')),
                        TextInput::make('billing_address.zip')->label(__('messages.zip_code')),
                        TextInput::make('billing_address.country')->label(__('messages.country')),
                    ])->columns(4)
                    ->collapsed(),

                Section::make(__('messages.financials'))
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('messages.subtotal'))
                            ->numeric()->prefix('€'),
                        TextInput::make('shipping_amount')
                            ->label(__('messages.shipping'))
                            ->numeric()->prefix('€'),
                        TextInput::make('tax_amount')
                            ->label(__('messages.tax_amount'))
                            ->numeric()->prefix('€'),
                        TextInput::make('discount_amount')
                            ->label(__('messages.discount_amount'))
                            ->numeric()->prefix('€'),
                        TextInput::make('total')
                            ->label(__('messages.total'))
                            ->numeric()->prefix('€')->required(),
                    ])->columns(5),

                Section::make(__('messages.checkout_payment'))
                    ->schema([
                        Select::make('payment_method')
                            ->label(__('messages.payment_method'))
                            ->options(PaymentMethod::class),
                        Select::make('payment_status')
                            ->label(__('messages.payment_status'))
                            ->options(PaymentStatus::class)
                            ->required(),
                    ])->columns(2),

                Section::make(__('messages.dates'))
                    ->schema([
                        DateTimePicker::make('created_at')
                            ->label(__('messages.created_at'))
                            ->disabled(),
                        DateTimePicker::make('updated_at')
                            ->label(__('messages.updated_at'))
                            ->disabled(),
                        DateTimePicker::make('shipped_at')
                            ->label(__('messages.shipped_at')),
                        DateTimePicker::make('delivered_at')
                            ->label(__('messages.delivered_at')),
                    ])->columns(4),
            ]);
    }
}
