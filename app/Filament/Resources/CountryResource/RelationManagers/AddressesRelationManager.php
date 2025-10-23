<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Enums\AddressType;
use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class AddressesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Form $form): Form
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('addresses.address_information'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('addresses.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label(__('addresses.type'))
                            ->options(AddressType::options())
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('addresses.first_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('addresses.last_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->label(__('addresses.company'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_vat')
                            ->label(__('addresses.company_vat'))
                            ->maxLength(50),
                    ]),
                Forms\Components\Section::make(__('addresses.address_details'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('address_line_1')
                            ->label(__('addresses.address_line_1'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line_2')
                            ->label(__('addresses.address_line_2'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('apartment')
                            ->label(__('addresses.apartment'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('floor')
                            ->label(__('addresses.floor'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('building')
                            ->label(__('addresses.building'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->label(__('addresses.city'))
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state')
                            ->label(__('addresses.state'))
                            ->maxLength(100),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('addresses.postal_code'))
                            ->required()
                            ->maxLength(20),
                    ]),
                Forms\Components\Section::make(__('addresses.contact_information'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label(__('addresses.phone'))
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label(__('addresses.email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('landmark')
                            ->label(__('addresses.landmark'))
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make(__('addresses.additional_information'))
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('addresses.notes'))
                            ->rows(3)
                            ->maxLength(1000),
                        Forms\Components\Textarea::make('instructions')
                            ->label(__('addresses.instructions'))
                            ->rows(3)
                            ->maxLength(1000),
                    ]),
                Forms\Components\Section::make(__('addresses.settings'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('addresses.is_default'))
                            ->helperText(__('addresses.is_default_help')),
                        Forms\Components\Toggle::make('is_billing')
                            ->label(__('addresses.is_billing'))
                            ->helperText(__('addresses.is_billing_help')),
                        Forms\Components\Toggle::make('is_shipping')
                            ->label(__('addresses.is_shipping'))
                            ->helperText(__('addresses.is_shipping_help')),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('addresses.is_active'))
                            ->helperText(__('addresses.is_active_help'))
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('addresses.full_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('addresses.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_line_1')
                    ->label(__('addresses.address_line_1'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('addresses.city'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label(__('addresses.postal_code'))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label(__('addresses.type'))
                    ->colors([
                        'info'    => fn (string $state): bool => $state === AddressType::BILLING->value,
                        'success' => fn (string $state): bool => $state === AddressType::SHIPPING->value,
                        'warning' => fn (string $state): bool => $state === AddressType::HOME->value,
                        'danger'  => fn (string $state): bool => $state === AddressType::WORK->value,
                        'gray'    => fn (): bool => true,
                    ]),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('addresses.is_default'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('addresses.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('addresses.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('addresses.type'))
                    ->options(AddressType::options()),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('addresses.is_default')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('addresses.is_active')),
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