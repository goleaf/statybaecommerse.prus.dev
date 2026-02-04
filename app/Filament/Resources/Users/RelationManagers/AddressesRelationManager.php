<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\AddressType;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(AddressType::options())
                    ->label(__('messages.type'))
                    ->required(),
                TextInput::make('first_name')
                    ->label(__('messages.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('messages.last_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('company')
                    ->label(__('messages.company'))
                    ->maxLength(255),
                TextInput::make('company_vat')
                    ->label(__('messages.company_vat'))
                    ->maxLength(50),
                TextInput::make('address_line_1')
                    ->label(__('messages.address_line_1'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line_2')
                    ->label(__('messages.address_line_2'))
                    ->maxLength(255),
                TextInput::make('city')
                    ->label(__('messages.city'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('postal_code')
                    ->label(__('messages.postal_code'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('country_code')
                    ->label(__('messages.country_code'))
                    ->required()
                    ->maxLength(2),
                TextInput::make('phone')
                    ->label(__('messages.phone'))
                    ->tel()
                    ->maxLength(20),
                TextInput::make('email')
                    ->label(__('messages.email'))
                    ->email()
                    ->maxLength(255),
                Toggle::make('is_default')
                    ->label(__('messages.is_default')),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address_line_1')
            ->columns([
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label(__('messages.name'))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('address_line_1')
                    ->label(__('messages.address_line_1'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('messages.city'))
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label(__('messages.postal_code'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('messages.country'))
                    ->searchable(),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label(__('messages.is_default')),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('messages.active')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                Action::make('set_default')
                    ->label(__('messages.set_as_default') ?? 'Set as Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Address $record) {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('messages.address_set_as_default') ?? 'Address set as default successfully.')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (Address $record): bool => $record->is_default),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
