<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
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
