<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'first_name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('admin.address_information'))
                    ->components([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('admin.first_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('admin.last_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company')
                            ->label(__('admin.company'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->label(__('admin.phone_number'))
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.address_details'))
                    ->components([
                        Forms\Components\TextInput::make('street_address')
                            ->label(__('admin.street_address'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('street_address_plus')
                            ->label(__('admin.apartment_suite'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label(__('admin.city'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('admin.postal_code'))
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Select::make('country_id')
                            ->label(__('admin.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('admin.default_address'))
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('admin.full_name'))
                    ->getStateUsing(fn($record) => trim("{$record->first_name} {$record->last_name}"))
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('street_address')
                    ->label(__('admin.address'))
                    ->limit(50),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('admin.city'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('admin.country'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('admin.default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('admin.country'))
                    ->relationship('country', 'name'),
                Tables\Filters\Filter::make('is_default')
                    ->label(__('admin.default_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_default', true)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.add_address')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
