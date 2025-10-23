<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Enums\AddressType;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AddressesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Schema $schema): Schema
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('first_name')
                    ->label(__('addresses.first_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('addresses.last_name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('company')
                    ->label(__('addresses.company'))
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('addresses.phone'))
                    ->tel()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('addresses.email'))
                    ->email()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('addresses.type'))
                    ->options(AddressType::options())
                    ->required(),
            ]),
            Grid::make(2)->schema([
                TextInput::make('address_line_1')
                    ->label(__('addresses.address_line_1'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line_2')
                    ->label(__('addresses.address_line_2'))
                    ->maxLength(255),
                TextInput::make('city')
                    ->label(__('addresses.city'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('state')
                    ->label(__('addresses.state'))
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->label(__('addresses.postal_code'))
                    ->maxLength(20),
                Select::make('country_id')
                    ->label(__('addresses.country'))
                    ->relationship('countryById', 'name')
                    ->searchable()
                    ->preload(),
            ]),
            Grid::make(2)->schema([
                Toggle::make('is_default')
                    ->label(__('addresses.is_default'))
                    ->default(false),
                Toggle::make('is_billing')
                    ->label(__('addresses.is_billing')),
                Toggle::make('is_shipping')
                    ->label(__('addresses.is_shipping')),
                Toggle::make('is_active')
                    ->label(__('addresses.is_active')),
            ]),
            Textarea::make('notes')
                ->label(__('addresses.notes'))
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('addresses.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address_line_1')
                    ->label(__('addresses.address_line_1'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label(__('addresses.city'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('countryById.name')
                    ->label(__('addresses.country'))
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('addresses.type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? AddressType::from($state)->label() : '—')
                    ->color(fn (?string $state): string => $state ? AddressType::from($state)->color() : 'gray'),
                IconColumn::make('is_default')
                    ->label(__('addresses.is_default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('addresses.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('addresses.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('addresses.type'))
                    ->options(AddressType::options()),
                TernaryFilter::make('is_default')
                    ->label(__('addresses.is_default')),
                TernaryFilter::make('is_active')
                    ->label(__('addresses.is_active')),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
