<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'cities';

    protected static ?string $title = 'Cities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('cities.basic_information'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('cities.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('cities.slug'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('cities.code'))
                            ->maxLength(50),
                        Forms\Components\Textarea::make('description')
                            ->label(__('cities.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('cities.postal_code'))
                            ->maxLength(20),
                    ]),
                Forms\Components\Section::make(__('cities.hierarchy'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label(__('cities.parent_city'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('level')
                            ->label(__('cities.level'))
                            ->options(function (): array {
                                $levels = trans('cities.levels');

                                if (! is_array($levels)) {
                                    return [];
                                }

                                return collect($levels)
                                    ->mapWithKeys(static fn ($label, $key) => [(string) $key => $label])
                                    ->toArray();
                            })
                            ->nullable(),
                    ]),
                Forms\Components\Section::make(__('cities.coordinates'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label(__('cities.latitude'))
                            ->numeric()
                            ->step(0.000001)
                            ->nullable(),
                        Forms\Components\TextInput::make('longitude')
                            ->label(__('cities.longitude'))
                            ->numeric()
                            ->step(0.000001)
                            ->nullable(),
                        Forms\Components\TextInput::make('timezone')
                            ->label(__('cities.timezone'))
                            ->maxLength(100)
                            ->nullable(),
                        Forms\Components\TextInput::make('population')
                            ->label(__('cities.population'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ]),
                Forms\Components\Section::make(__('cities.settings'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('cities.is_active'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('cities.is_active'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_capital')
                            ->label(__('cities.is_capital')),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('cities.is_default')),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('cities.sort_order'))
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('cities.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('cities.slug'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('cities.code'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('level')
                    ->label(__('cities.level'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('population')
                    ->label(__('cities.population'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label(__('cities.postal_code'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_capital')
                    ->label(__('cities.is_capital'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('cities.is_default'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('cities.is_active'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('cities.is_active'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('cities.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label(__('cities.level'))
                    ->options(function (): array {
                        $levels = trans('cities.levels');

                        if (! is_array($levels)) {
                            return [];
                        }

                        return collect($levels)
                            ->mapWithKeys(static fn ($label, $key) => [(string) $key => $label])
                            ->toArray();
                    }),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('cities.is_active')),
                Tables\Filters\TernaryFilter::make('is_capital')
                    ->label(__('cities.is_capital')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('cities.is_default')),
            ])
            ->headerActions([
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderBy('name'))
            ->defaultSort('sort_order');
    }
}
