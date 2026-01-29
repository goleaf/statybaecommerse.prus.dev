<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Schemas;

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
                Grid::make(1)
                    ->schema([
                        Section::make(__('messages.checkout_order_information'))
                            ->schema([
                                TextEntry::make('number')
                                    ->label(__('messages.order_number')),
                                TextEntry::make('status')
                                    ->label(__('messages.status'))
                                    ->badge(),
                                TextEntry::make('currency')
                                    ->label(__('messages.currency')),
                                TextEntry::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->dateTime(),
                            ])->columns(4),

                        Section::make(__('messages.checkout_shipping_address'))
                            ->schema([
                                TextEntry::make('shipping_address.first_name')->label(__('messages.first_name')),
                                TextEntry::make('shipping_address.last_name')->label(__('messages.last_name')),
                                TextEntry::make('shipping_address.email')->label(__('messages.email')),
                                TextEntry::make('shipping_address.phone')->label(__('messages.phone')),
                                TextEntry::make('shipping_address.street')->label(__('messages.street'))->columnSpanFull(),
                                TextEntry::make('shipping_address.city')->label(__('messages.city')),
                                TextEntry::make('shipping_address.zip')->label(__('messages.zip_code')),
                                TextEntry::make('shipping_address.country')->label(__('messages.country')),
                            ])->columns(4),

                        Section::make(__('messages.checkout_billing_address'))
                            ->schema([
                                TextEntry::make('billing_address.first_name')->label(__('messages.first_name')),
                                TextEntry::make('billing_address.last_name')->label(__('messages.last_name')),
                                TextEntry::make('billing_address.email')->label(__('messages.email')),
                                TextEntry::make('billing_address.phone')->label(__('messages.phone')),
                                TextEntry::make('billing_address.street')->label(__('messages.street'))->columnSpanFull(),
                                TextEntry::make('billing_address.city')->label(__('messages.city')),
                                TextEntry::make('billing_address.zip')->label(__('messages.zip_code')),
                                TextEntry::make('billing_address.country')->label(__('messages.country')),
                            ])->columns(4),

                        Section::make(__('messages.customer'))
                            ->schema([
                                TextEntry::make('user.name')->label(__('messages.name')),
                                TextEntry::make('user.email')->label(__('messages.email')),
                            ])->columns(2),

                        Section::make(__('messages.financials'))
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label(__('messages.subtotal'))
                                    ->money('EUR'),
                                TextEntry::make('shipping_amount')
                                    ->label(__('messages.shipping'))
                                    ->money('EUR'),
                                TextEntry::make('tax_amount')
                                    ->label(__('messages.tax_amount'))
                                    ->money('EUR'),
                                TextEntry::make('discount_amount')
                                    ->label(__('messages.discount_amount'))
                                    ->money('EUR'),
                                TextEntry::make('total')
                                    ->label(__('messages.total'))
                                    ->money('EUR')->weight('bold'),
                            ])->columns(5),

                        Section::make(__('messages.checkout_payment'))
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->label(__('messages.payment_method'))
                                    ->badge(),
                                TextEntry::make('payment_status')
                                    ->label(__('messages.payment_status'))
                                    ->badge(),
                            ])->columns(2),

                        Section::make(__('messages.status'))
                            ->schema([
                                TextEntry::make('fulfillment_status')
                                    ->label(__('messages.fulfillment_status')),
                                TextEntry::make('shipped_at')
                                    ->label(__('messages.shipped_at'))
                                    ->dateTime(),
                                TextEntry::make('delivered_at')
                                    ->label(__('messages.delivered_at'))
                                    ->dateTime(),
                            ])->columns(3),
                    ]),
            ]);
    }
}
