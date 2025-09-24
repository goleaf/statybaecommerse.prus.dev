<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use App\Models\Country;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

final class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationLabel = 'Cities';

    protected static ?string $modelLabel = 'City';

    protected static ?string $pluralModelLabel = 'Cities';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('cities.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('cities.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('cities.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('cities.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set, $operation) {
                                    if ($operation === 'create' && $state) {
                                        $set('slug', \Str::slug($state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label(__('cities.slug'))
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('cities.slug_help')),
                        ]),
                    Select::make('country_id')
                        ->label(__('cities.country'))
                        ->relationship('country', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $country = Country::find($state);
                                if ($country) {
                                    $set('country_code', $country->code);
                                    $set('currency_code', $country->currency_code);
                                    $set('language_code', $country->language_code);
                                    $set('phone_code', $country->phone_code);
                                }
                            }
                        }),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('cities.code'))
                                ->maxLength(10)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('cities.code_help')),
                            TextInput::make('country_code')
                                ->label(__('cities.country_code'))
                                ->maxLength(3)
                                ->disabled(),
                            TextInput::make('state_province')
                                ->label(__('cities.state_province'))
                                ->maxLength(100),
                        ]),
                    Textarea::make('description')
                        ->label(__('cities.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('cities.coordinates'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('latitude')
                                ->label(__('cities.latitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-90)
                                ->maxValue(90)
                                ->helperText(__('cities.latitude_help')),
                            TextInput::make('longitude')
                                ->label(__('cities.longitude'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(-180)
                                ->maxValue(180)
                                ->helperText(__('cities.longitude_help')),
                        ]),
                ]),
            Section::make(__('cities.demographics'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('population')
                                ->label(__('cities.population'))
                                ->numeric()
                                ->minValue(0)
                                ->helperText(__('cities.population_help')),
                            TextInput::make('area')
                                ->label(__('cities.area'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('cities.area_help')),
                            TextInput::make('density')
                                ->label(__('cities.density'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('cities.density_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('elevation')
                                ->label(__('cities.elevation'))
                                ->numeric()
                                ->step(0.01)
                                ->helperText(__('cities.elevation_help')),
                            TextInput::make('timezone')
                                ->label(__('cities.timezone'))
                                ->maxLength(100)
                                ->helperText(__('cities.timezone_help')),
                        ]),
                ]),
            Section::make(__('cities.localization'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('currency_code')
                                ->label(__('cities.currency_code'))
                                ->maxLength(3)
                                ->helperText(__('cities.currency_code_help')),
                            TextInput::make('language_code')
                                ->label(__('cities.language_code'))
                                ->maxLength(5)
                                ->helperText(__('cities.language_code_help')),
                            TextInput::make('phone_code')
                                ->label(__('cities.phone_code'))
                                ->maxLength(10)
                                ->helperText(__('cities.phone_code_help')),
                        ]),
                ]),
            Section::make(__('cities.hierarchy'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('parent_id')
                                ->label(__('cities.parent_city'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText(__('cities.parent_city_help')),
                            TextInput::make('level')
                                ->label(__('cities.level'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(10)
                                ->default(0)
                                ->helperText(__('cities.level_help')),
                        ]),
                ]),
            Section::make(__('cities.settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('cities.is_active'))
                                ->default(true),
                            Toggle::make('is_capital')
                                ->label(__('cities.is_capital')),
                            Toggle::make('is_default')
                                ->label(__('cities.is_default')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('cities.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->helperText(__('cities.sort_order_help')),
                            Select::make('type')
                                ->label(__('cities.type'))
                                ->options([
                                    'metropolitan' => __('cities.types.metropolitan'),
                                    'urban' => __('cities.types.urban'),
                                    'rural' => __('cities.types.rural'),
                                    'suburban' => __('cities.types.suburban'),
                                    'industrial' => __('cities.types.industrial'),
                                    'tourist' => __('cities.types.tourist'),
                                ])
                                ->searchable()
                                ->helperText(__('cities.type_help')),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('cities.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (City $record): string => $record->description ? \Str::limit($record->description, 50) : ''),
                TextColumn::make('code')
                    ->label(__('cities.code'))
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('country.name')
                    ->label(__('cities.country'))
                    ->color('blue')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label(__('cities.country_code'))
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('state_province')
                    ->label(__('cities.state_province'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('population')
                    ->label(__('cities.population'))
                    ->numeric()
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('area')
                    ->label(__('cities.area'))
                    ->numeric()
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2).' km²' : '-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('density')
                    ->label(__('cities.density'))
                    ->numeric()
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2).'/km²' : '-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('cities.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_capital')
                    ->label(__('cities.is_capital'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('cities.is_default'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('cities.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'metropolitan' => 'purple',
                        'urban' => 'blue',
                        'rural' => 'green',
                        'suburban' => 'orange',
                        'industrial' => 'red',
                        'tourist' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("cities.types.{$state}"))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('level')
                    ->label(__('cities.level'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'blue',
                        1 => 'green',
                        2 => 'yellow',
                        3 => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => __("cities.levels.{$state}"))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('timezone')
                    ->label(__('cities.timezone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency_code')
                    ->label(__('cities.currency_code'))
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('language_code')
                    ->label(__('cities.language_code'))
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone_code')
                    ->label(__('cities.phone_code'))
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parent.name')
                    ->label(__('cities.parent_city'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(__('cities.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('cities.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cities.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('cities.active_only'))
                    ->falseLabel(__('cities.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_capital')
                    ->trueLabel(__('cities.capital_only'))
                    ->falseLabel(__('cities.non_capital_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('cities.default_only'))
                    ->falseLabel(__('cities.non_default_only'))
                    ->native(false),
                SelectFilter::make('type')
                    ->options([
                        'metropolitan' => __('cities.types.metropolitan'),
                        'urban' => __('cities.types.urban'),
                        'rural' => __('cities.types.rural'),
                        'suburban' => __('cities.types.suburban'),
                        'industrial' => __('cities.types.industrial'),
                        'tourist' => __('cities.types.tourist'),
                    ]),
                SelectFilter::make('level')
                    ->options([
                        0 => __('cities.levels.0'),
                        1 => __('cities.levels.1'),
                        2 => __('cities.levels.2'),
                        3 => __('cities.levels.3'),
                        4 => __('cities.levels.4'),
                        5 => __('cities.levels.5'),
                    ]),
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('currency_code')
                    ->options(function () {
                        return City::distinct()->pluck('currency_code', 'currency_code')->filter()->toArray();
                    })
                    ->searchable(),
                SelectFilter::make('language_code')
                    ->options(function () {
                        return City::distinct()->pluck('language_code', 'language_code')->filter()->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (City $record): string => $record->is_active ? __('cities.deactivate') : __('cities.activate'))
                    ->icon(fn (City $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (City $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (City $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('cities.activated_successfully') : __('cities.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_capital')
                    ->label(fn (City $record): string => $record->is_capital ? __('cities.remove_capital') : __('cities.set_capital'))
                    ->icon(fn (City $record): string => $record->is_capital ? 'heroicon-o-building-office' : 'heroicon-o-building-office-2')
                    ->color(fn (City $record): string => $record->is_capital ? 'warning' : 'success')
                    ->action(function (City $record): void {
                        $record->update(['is_capital' => ! $record->is_capital]);
                        Notification::make()
                            ->title($record->is_capital ? __('cities.set_as_capital_success') : __('cities.removed_from_capital_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_default')
                    ->label(fn (City $record): string => $record->is_default ? __('cities.remove_default') : __('cities.set_default'))
                    ->icon(fn (City $record): string => $record->is_default ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn (City $record): string => $record->is_default ? 'warning' : 'success')
                    ->action(function (City $record): void {
                        $record->update(['is_default' => ! $record->is_default]);
                        Notification::make()
                            ->title($record->is_default ? __('cities.set_as_default_success') : __('cities.removed_from_default_success'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('cities.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('cities.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('cities.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('cities.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('set_capital')
                        ->label(__('cities.set_capital_selected'))
                        ->icon('heroicon-o-building-office-2')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_capital' => true]);
                            Notification::make()
                                ->title(__('cities.bulk_set_capital_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('remove_capital')
                        ->label(__('cities.remove_capital_selected'))
                        ->icon('heroicon-o-building-office')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_capital' => false]);
                            Notification::make()
                                ->title(__('cities.bulk_remove_capital_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->poll('30s');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'view' => Pages\ViewCity::route('/{record}'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
