<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\RelationManagers;

use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class CustomersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $title = 'Customers';

    public function form(Form $form): Form
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('customers.basic_information'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('customers.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('customers.email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('customers.phone'))
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('addresses.postal_code'))
                            ->maxLength(20),
                    ]),
                Forms\Components\Section::make(__('customers.address_information'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label(__('customers.address'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('city_id')
                            ->label(__('cities.single'))
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(__('customers.account_settings'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('customers.is_active'))
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('customers.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('customers.email'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('customers.phone'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label(__('cities.single'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city_id')
                    ->label(__('cities.single'))
                    ->relationship('city', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('customers.is_active')),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('deleted_at'))
            ->defaultSort('created_at', 'desc');
    }
}