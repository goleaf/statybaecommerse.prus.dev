<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ListEntry::make('customerQuickLinks')
                ->heading(__('Quick links'))
                ->state(function (Customer $record): array {
                    $items = [];

                    if (filled($record->email)) {
                        $items[] = ListItem::make()
                            ->id('customer-email-link')
                            ->label(__('Email :name', ['name' => $record->name]))
                            ->icon('heroicon-m-envelope')
                            ->color('primary')
                            ->url('mailto:' . $record->email)
                            ->tooltip(__('Compose an email to :email', ['email' => $record->email]))
                            ->toArray();
                    }

                    if (filled($record->phone)) {
                        $items[] = ListItem::make()
                            ->id('customer-phone-link')
                            ->label(__('Call :name', ['name' => $record->name]))
                            ->icon('heroicon-m-phone')
                            ->color('success')
                            ->url('tel:' . preg_replace('/[^\d+]/', '', (string) $record->phone))
                            ->tooltip(__('Dial :phone', ['phone' => $record->phone]))
                            ->toArray();
                    }

                    return $items;
                }),
            ListEntry::make('customerOrders')
                ->heading(__('customers.orders'))
                ->list()
                ->state(function (Customer $record): array {
                    $record->loadMissing(['orders']);

                    return $record->orders
                        ->sortByDesc('created_at')
                        ->map(function (Order $order): array {
                            return ListItem::make()
                                ->id('customer-order-' . $order->getKey())
                                ->label(__('Order #:number', ['number' => $order->number]))
                                ->icon('heroicon-m-receipt-percent')
                                ->color('info')
                                ->url(route('account.orders.detail', ['number' => $order->number]))
                                ->tooltip(__('Placed on :date', [
                                    'date' => optional($order->created_at)->toFormattedDateString(),
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
            Section::make(__('customers.address_information'))
                ->schema([
                    TableRepeatableEntry::make('addresses')
                        ->label(__('customers.address_information'))
                        ->translateLabel()
                        ->state(function (Customer $record): array {
                            $record->loadMissing(['addresses.country', 'addresses.cityById']);

                            return $record->addresses
                                ->map(fn (Address $address): array => [
                                    'name'        => $address->full_name,
                                    'address'     => $address->formatted_address ?? $address->address_line_1,
                                    'city'        => $address->city ?: $address->cityById?->name,
                                    'postal_code' => $address->postal_code,
                                    'country'     => $address->country?->name ?? $address->country_code,
                                    'phone'       => $address->phone,
                                ])
                                ->values()
                                ->all();
                        })
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('customers.name'))
                                ->translateLabel(),
                            TextEntry::make('address')
                                ->label(__('customers.address'))
                                ->translateLabel(),
                            TextEntry::make('city')
                                ->label(__('customers.city'))
                                ->translateLabel(),
                            TextEntry::make('postal_code')
                                ->label(__('customers.postal_code'))
                                ->translateLabel(),
                            TextEntry::make('country')
                                ->label(__('customers.country'))
                                ->translateLabel(),
                            TextEntry::make('phone')
                                ->label(__('customers.phone'))
                                ->translateLabel(),
                        ])
                        ->striped()
                        ->showIndex(),
                ])
                ->columns(1),
        ]);
    }
}
