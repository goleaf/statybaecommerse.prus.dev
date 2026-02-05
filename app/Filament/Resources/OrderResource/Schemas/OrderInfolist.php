<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_shipping_address'))
                    ->schema([
                        TextEntry::make('shipping_address.first_name')->label(__('messages.first_name')),
                        TextEntry::make('shipping_address.last_name')->label(__('messages.last_name')),
                        TextEntry::make('shipping_address.email')->label(__('messages.email')),
                        TextEntry::make('shipping_address.phone')->label(__('messages.phone')),
                        TextEntry::make('shipping_address.street')
                            ->label(__('messages.street'))
                            ->state(static fn (Order $record): ?string => self::resolveStreet($record->shipping_address))
                            ->columnSpanFull(),
                        TextEntry::make('shipping_address.city')->label(__('messages.city')),
                        TextEntry::make('shipping_address.zip')
                            ->label(__('messages.zip_code'))
                            ->state(static fn (Order $record): ?string => self::resolvePostalCode($record->shipping_address)),
                        TextEntry::make('shipping_address.country')->label(__('messages.country')),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_billing_address'))
                    ->schema([
                        TextEntry::make('billing_address.first_name')->label(__('messages.first_name')),
                        TextEntry::make('billing_address.last_name')->label(__('messages.last_name')),
                        TextEntry::make('billing_address.email')->label(__('messages.email')),
                        TextEntry::make('billing_address.phone')->label(__('messages.phone')),
                        TextEntry::make('billing_address.street')
                            ->label(__('messages.street'))
                            ->state(static fn (Order $record): ?string => self::resolveStreet($record->billing_address))
                            ->columnSpanFull(),
                        TextEntry::make('billing_address.city')->label(__('messages.city')),
                        TextEntry::make('billing_address.zip')
                            ->label(__('messages.zip_code'))
                            ->state(static fn (Order $record): ?string => self::resolvePostalCode($record->billing_address)),
                        TextEntry::make('billing_address.country')->label(__('messages.country')),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('messages.customer'))
                    ->schema([
                        TextEntry::make('user.name')->label(__('messages.name')),
                        TextEntry::make('user.email')->label(__('messages.email')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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
                    ])
                    ->columns(5)
                    ->columnSpanFull(),

                Section::make(__('messages.checkout_payment'))
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label(__('messages.payment_method'))
                            ->badge(),
                        TextEntry::make('payment_status')
                            ->label(__('messages.payment_status'))
                            ->badge(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    private static function resolveStreet(?array $address): ?string
    {
        if (! is_array($address) || $address === []) {
            return null;
        }

        $street = $address['street'] ?? null;
        if (is_string($street) && $street !== '') {
            return $street;
        }

        $line1 = $address['address_line_1'] ?? null;
        $line2 = $address['address_line_2'] ?? null;

        $line1 = is_string($line1) ? trim($line1) : '';
        $line2 = is_string($line2) ? trim($line2) : '';

        if ($line1 === '' && $line2 === '') {
            return null;
        }

        return trim($line1 . ($line2 !== '' ? ', ' . $line2 : ''));
    }

    private static function resolvePostalCode(?array $address): ?string
    {
        if (! is_array($address) || $address === []) {
            return null;
        }

        $zip = $address['zip'] ?? null;
        if (is_string($zip) && $zip !== '') {
            return $zip;
        }

        $postalCode = $address['postal_code'] ?? null;

        return is_string($postalCode) && $postalCode !== '' ? $postalCode : null;
    }
}