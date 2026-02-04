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
                Section::make(__('admin.document_templates.fields.settings'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.address'))
                    ->schema([
                        TextInput::make('address')
                            ->label(__('messages.address'))
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('messages.zip_code'))
                            ->maxLength(255),
                        Select::make('city_id')
                            ->label(__('messages.city'))
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('country_id')
                            ->label(__('messages.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Select::make('company_id')
                            ->label(__('messages.companies'))
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->required(),
                        KeyValue::make('metadata')
                            ->label(__('messages.Value')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
