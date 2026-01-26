<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
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
                                Section::make('Order Information')
                                    ->schema([
                                        TextEntry::make('number'),
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('currency'),
                                        TextEntry::make('created_at')
                                            ->dateTime(),
                                    ])->columns(2),

                                Section::make('Shipping Address')
                                    ->schema([
                                        TextEntry::make('shipping_address.first_name')->label('First Name'),
                                        TextEntry::make('shipping_address.last_name')->label('Last Name'),
                                        TextEntry::make('shipping_address.email')->label('Email'),
                                        TextEntry::make('shipping_address.phone')->label('Phone'),
                                        TextEntry::make('shipping_address.street')->label('Street')->columnSpanFull(),
                                        TextEntry::make('shipping_address.city')->label('City'),
                                        TextEntry::make('shipping_address.zip')->label('Zip'),
                                        TextEntry::make('shipping_address.country')->label('Country'),
                                    ])->columns(2),

                                Section::make('Billing Address')
                                    ->schema([
                                        TextEntry::make('billing_address.first_name')->label('First Name'),
                                        TextEntry::make('billing_address.last_name')->label('Last Name'),
                                        TextEntry::make('billing_address.email')->label('Email'),
                                        TextEntry::make('billing_address.phone')->label('Phone'),
                                        TextEntry::make('billing_address.street')->label('Street')->columnSpanFull(),
                                        TextEntry::make('billing_address.city')->label('City'),
                                        TextEntry::make('billing_address.zip')->label('Zip'),
                                        TextEntry::make('billing_address.country')->label('Country'),
                                    ])->columns(2)
                                    ->collapsed(),
                            ]),

                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Customer')
                                    ->schema([
                                        TextEntry::make('user.name')->label('Name'),
                                        TextEntry::make('user.email')->label('Email'),
                                    ]),

                                Section::make('Financials')
                                    ->schema([
                                        TextEntry::make('subtotal')->money('EUR'),
                                        TextEntry::make('shipping_amount')->money('EUR'),
                                        TextEntry::make('tax_amount')->money('EUR'),
                                        TextEntry::make('discount_amount')->money('EUR'),
                                        TextEntry::make('total')->money('EUR')->weight('bold'),
                                    ]),

                                Section::make('Payment')
                                    ->schema([
                                        TextEntry::make('payment_method')->badge(),
                                        TextEntry::make('payment_status')->badge(),
                                    ]),

                                Section::make('Status')
                                    ->schema([
                                        TextEntry::make('fulfillment_status'),
                                        TextEntry::make('shipped_at')->dateTime(),
                                        TextEntry::make('delivered_at')->dateTime(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}