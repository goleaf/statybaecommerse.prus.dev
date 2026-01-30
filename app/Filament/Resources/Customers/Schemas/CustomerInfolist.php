<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.navigation.customers'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('messages.name')),
                        TextEntry::make('email')
                            ->label(__('messages.email')),
                        TextEntry::make('phone')
                            ->label(__('messages.phone')),
                    ])->columns(2),

                Section::make(__('messages.Profile'))
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('messages.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('messages.last_name')),
                    ])->columns(2),
            ]);
    }
}
