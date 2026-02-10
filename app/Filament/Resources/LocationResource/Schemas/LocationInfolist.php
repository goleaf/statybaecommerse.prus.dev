<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\Schemas;

use App\Models\Location;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.locations_page.details_title'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('messages.code')),
                                TextEntry::make('name')
                                    ->label(__('messages.name')),
                                TextEntry::make('slug')
                                    ->label(__('messages.slug'))
                                    ->placeholder('-'),
                                TextEntry::make('type')
                                    ->label(__('messages.type'))
                                    ->formatStateUsing(static fn (?string $state): string => $state ? Str::headline($state) : '-'),
                                TextEntry::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric(),
                                IconEntry::make('is_enabled')
                                    ->label(__('messages.is_enabled'))
                                    ->boolean(),
                                IconEntry::make('is_default')
                                    ->label(__('messages.is_default'))
                                    ->boolean(),
                            ]),
                        TextEntry::make('description')
                            ->label(__('messages.description'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.address'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('address_line_1')
                                    ->label(__('messages.address_line_1'))
                                    ->placeholder('-'),
                                TextEntry::make('address_line_2')
                                    ->label(__('messages.address_line_2'))
                                    ->placeholder('-'),
                                TextEntry::make('city')
                                    ->label(__('messages.city'))
                                    ->placeholder('-'),
                                TextEntry::make('state')
                                    ->label(__('messages.state'))
                                    ->placeholder('-'),
                                TextEntry::make('postal_code')
                                    ->label(__('messages.postal_code'))
                                    ->placeholder('-'),
                                TextEntry::make('country_code')
                                    ->label(__('messages.country_code'))
                                    ->placeholder('-'),
                                TextEntry::make('country.name')
                                    ->label(__('admin.locations_page.fields.country'))
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.contact_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('phone')
                                    ->label(__('messages.phone'))
                                    ->placeholder('-'),
                                TextEntry::make('email')
                                    ->label(__('messages.email'))
                                    ->placeholder('-'),
                                TextEntry::make('contact_info.map_url')
                                    ->label(__('messages.map_url'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.coordinates'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('latitude')
                                    ->label(__('messages.latitude'))
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('longitude')
                                    ->label(__('messages.longitude'))
                                    ->numeric()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.hours'))
                    ->schema([
                        TextEntry::make('opening_hours')
                            ->label(__('messages.hours'))
                            ->formatStateUsing(static function ($state): string {
                                if (blank($state)) {
                                    return '-';
                                }

                                return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '-';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('messages.status'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->label(__('messages.updated_at'))
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('deleted_at')
                                    ->label(__('messages.deleted_at'))
                                    ->dateTime()
                                    ->visible(static fn (Location $record): bool => $record->trashed()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
