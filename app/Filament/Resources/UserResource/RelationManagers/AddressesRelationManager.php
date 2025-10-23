<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;


use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

class AddressesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    protected static ?string $modelLabel = 'Address';

    protected static ?string $pluralModelLabel = 'Addresses';

    public function form(Schema $schema): Schema   
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label(__('addresses.fields.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('addresses.fields.last_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('street_address')
                    ->label(__('addresses.fields.street_address'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('street_address_plus')
                    ->label(__('addresses.fields.street_address_plus'))
                    ->maxLength(255),
                TextInput::make('city')
                    ->label(__('addresses.fields.city'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->label(__('addresses.fields.postal_code'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('phone')
                    ->label(__('addresses.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Select::make('country_id')
                    ->label(__('addresses.fields.country'))
                    ->relationship('country', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Toggle::make('is_default')
                    ->label(__('addresses.fields.is_default')),
            ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('addresses.fields.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('addresses.fields.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('street_address')
                    ->label(__('addresses.fields.street_address'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('city')
                    ->label(__('addresses.fields.city'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postal_code')
                    ->label(__('addresses.fields.postal_code'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('addresses.fields.country'))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('addresses.fields.is_default'))
                    ->boolean(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit addresses')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit customer addresses')
                    ->modalWidth('5xl')
                    // Facilitate bulk address maintenance for a user via a concise repeater-driven modal.
                    ->configureRepeater(static function (Repeater $repeater): Repeater {
                        return $repeater
                            ->collapsible()
                            ->defaultItems(0)
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('first_name')
                                    ->label(__('addresses.fields.first_name'))
                                    ->maxLength(255)
                                    ->required(),
                                TextInput::make('last_name')
                                    ->label(__('addresses.fields.last_name'))
                                    ->maxLength(255)
                                    ->required(),
                                TextInput::make('street_address')
                                    ->label(__('addresses.fields.street_address'))
                                    ->maxLength(255)
                                    ->required(),
                                TextInput::make('street_address_plus')
                                    ->label(__('addresses.fields.street_address_plus'))
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label(__('addresses.fields.city'))
                                    ->maxLength(255)
                                    ->required(),
                                TextInput::make('postal_code')
                                    ->label(__('addresses.fields.postal_code'))
                                    ->maxLength(20)
                                    ->required(),
                                TextInput::make('phone')
                                    ->label(__('addresses.fields.phone'))
                                    ->tel()
                                    ->maxLength(255),
                                Select::make('country_id')
                                    ->label(__('addresses.fields.country'))
                                    ->relationship('country', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Toggle::make('is_default')
                                    ->label(__('addresses.fields.is_default')),
                            ]);
                    }),
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}