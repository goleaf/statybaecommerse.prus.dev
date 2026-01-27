<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->maxLength(255),
                        Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->required(),
                        KeyValue::make('metadata'),
                    ]),
            ]);
    }
}
