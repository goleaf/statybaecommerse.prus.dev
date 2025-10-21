<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Address;
use App\Models\Customer;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('customers.address_information'))
                ->schema([
                    TableRepeatableEntry::make('addresses')
                        ->label(__('customers.address_information'))
                        ->translateLabel()
                        ->state(function (Customer $record): array {
                            $record->loadMissing(['addresses.country', 'addresses.cityById']);

                            return $record->addresses
                                ->map(fn (Address $address): array => [
                                    'name' => $address->full_name,
                                    'address' => $address->formatted_address ?? $address->address_line_1,
                                    'city' => $address->city ?: $address->cityById?->name,
                                    'postal_code' => $address->postal_code,
                                    'country' => $address->country?->name ?? $address->country_code,
                                    'phone' => $address->phone,
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
