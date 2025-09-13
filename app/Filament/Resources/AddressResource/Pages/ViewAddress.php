<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\BadgeEntry;

final class ViewAddress extends ViewRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('translations.address_information'))
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('translations.user'))
                            ->url(fn ($record) => \App\Filament\Resources\UserResource::getUrl('view', ['record' => $record->user_id])),

                        BadgeEntry::make('type')
                            ->label(__('translations.type'))
                            ->formatStateUsing(fn ($state) => $state->label())
                            ->color(fn ($state) => match($state) {
                                \App\Enums\AddressType::SHIPPING => 'primary',
                                \App\Enums\AddressType::BILLING => 'success',
                                \App\Enums\AddressType::HOME => 'warning',
                                \App\Enums\AddressType::WORK => 'info',
                                \App\Enums\AddressType::OTHER => 'secondary',
                                default => 'gray',
                            }),

                        TextEntry::make('display_name')
                            ->label(__('translations.full_name')),

                        TextEntry::make('company_name')
                            ->label(__('translations.company'))
                            ->visible(fn ($record) => $record->company_name),

                        TextEntry::make('company_vat')
                            ->label(__('translations.company_vat'))
                            ->visible(fn ($record) => $record->company_vat),
                    ])
                    ->columns(2),

                Section::make(__('translations.address_details'))
                    ->schema([
                        TextEntry::make('address_line_1')
                            ->label(__('translations.address_line_1')),

                        TextEntry::make('address_line_2')
                            ->label(__('translations.address_line_2'))
                            ->visible(fn ($record) => $record->address_line_2),

                        TextEntry::make('apartment')
                            ->label(__('translations.apartment'))
                            ->visible(fn ($record) => $record->apartment),

                        TextEntry::make('floor')
                            ->label(__('translations.floor'))
                            ->visible(fn ($record) => $record->floor),

                        TextEntry::make('building')
                            ->label(__('translations.building'))
                            ->visible(fn ($record) => $record->building),

                        TextEntry::make('city')
                            ->label(__('translations.city')),

                        TextEntry::make('state')
                            ->label(__('translations.state'))
                            ->visible(fn ($record) => $record->state),

                        TextEntry::make('postal_code')
                            ->label(__('translations.postal_code')),

                        TextEntry::make('country.name')
                            ->label(__('translations.country')),

                        TextEntry::make('zone.name')
                            ->label(__('translations.zone'))
                            ->visible(fn ($record) => $record->zone),

                        TextEntry::make('region.name')
                            ->label(__('translations.region'))
                            ->visible(fn ($record) => $record->region),

                        TextEntry::make('cityById.name')
                            ->label(__('translations.city'))
                            ->visible(fn ($record) => $record->cityById),
                    ])
                    ->columns(2),

                Section::make(__('translations.contact_information'))
                    ->schema([
                        TextEntry::make('phone')
                            ->label(__('translations.phone'))
                            ->visible(fn ($record) => $record->phone),

                        TextEntry::make('email')
                            ->label(__('translations.email'))
                            ->visible(fn ($record) => $record->email),

                        TextEntry::make('landmark')
                            ->label(__('translations.landmark'))
                            ->visible(fn ($record) => $record->landmark),
                    ])
                    ->columns(2),

                Section::make(__('translations.additional_information'))
                    ->schema([
                        TextEntry::make('notes')
                            ->label(__('translations.notes'))
                            ->visible(fn ($record) => $record->notes)
                            ->columnSpanFull(),

                        TextEntry::make('instructions')
                            ->label(__('translations.instructions'))
                            ->visible(fn ($record) => $record->instructions)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('translations.settings'))
                    ->schema([
                        IconEntry::make('is_default')
                            ->label(__('translations.is_default'))
                            ->boolean(),

                        IconEntry::make('is_billing')
                            ->label(__('translations.is_billing'))
                            ->boolean(),

                        IconEntry::make('is_shipping')
                            ->label(__('translations.is_shipping'))
                            ->boolean(),

                        IconEntry::make('is_active')
                            ->label(__('translations.is_active'))
                            ->boolean(),
                    ])
                    ->columns(4),

                Section::make(__('translations.formatted_address'))
                    ->schema([
                        TextEntry::make('formatted_address')
                            ->label('')
                            ->formatStateUsing(fn ($record) => $record->formatted_address)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('translations.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('translations.created_at'))
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label(__('translations.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
