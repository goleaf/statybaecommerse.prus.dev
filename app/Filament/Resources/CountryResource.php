<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'countries.navigation.countries';

    protected static ?string $modelLabel = 'countries.models.country';

    protected static ?string $pluralModelLabel = 'countries.models.countries';

    public static function getNavigationLabel(): string
    {
        return __('countries.navigation.countries');
    }

    public static function getModelLabel(): string
    {
        return __('countries.models.country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('countries.models.countries');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('countries.sections.basic_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('countries.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                TextInput::make('name_official')
                                    ->label(__('countries.fields.name_official'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextInput::make('cca2')
                                    ->label(__('countries.fields.cca2'))
                                    ->required()
                                    ->maxLength(2)
                                    ->unique(ignoreRecord: true)
                                    ->helperText(__('countries.tooltips.cca2')),
                                TextInput::make('cca3')
                                    ->label(__('countries.fields.cca3'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.tooltips.cca3')),
                                TextInput::make('ccn3')
                                    ->label(__('countries.fields.ccn3'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.tooltips.ccn3')),
                                TextInput::make('iso_code')
                                    ->label(__('countries.fields.iso_code'))
                                    ->maxLength(10),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('region')
                                    ->label(__('countries.fields.region'))
                                    ->maxLength(100),
                                TextInput::make('subregion')
                                    ->label(__('countries.fields.subregion'))
                                    ->maxLength(100),
                            ]),
                        Textarea::make('description')
                            ->label(__('countries.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('countries.sections.location_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__('countries.fields.latitude'))
                                    ->numeric()
                                    ->step(0.000001)
                                    ->helperText(__('countries.validation.latitude_numeric')),
                                TextInput::make('longitude')
                                    ->label(__('countries.fields.longitude'))
                                    ->numeric()
                                    ->step(0.000001)
                                    ->helperText(__('countries.validation.longitude_numeric')),
                                TextInput::make('timezone')
                                    ->label(__('countries.fields.timezone'))
                                    ->maxLength(50),
                            ]),
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('timezones')
                                    ->label(__('countries.fields.timezones'))
                                    ->keyLabel('Timezone')
                                    ->valueLabel('Offset')
                                    ->columnSpan(1),
                                KeyValue::make('languages')
                                    ->label(__('countries.fields.languages'))
                                    ->keyLabel('Language')
                                    ->valueLabel('Code')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2),
                Section::make(__('countries.sections.economic_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label(__('countries.fields.currency_code'))
                                    ->maxLength(3)
                                    ->helperText(__('countries.validation.currency_code_invalid')),
                                TextInput::make('currency_symbol')
                                    ->label(__('countries.fields.currency_symbol'))
                                    ->maxLength(5),
                                TextInput::make('phone_code')
                                    ->label(__('countries.fields.phone_code'))
                                    ->maxLength(10)
                                    ->helperText(__('countries.validation.phone_code_invalid')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('phone_calling_code')
                                    ->label(__('countries.fields.phone_calling_code'))
                                    ->maxLength(10),
                                TextInput::make('vat_rate')
                                    ->label(__('countries.fields.vat_rate'))
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->helperText(__('countries.validation.vat_rate_numeric')),
                                Hidden::make('currencies')
                                    ->default([]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_eu_member')
                                    ->label(__('countries.fields.is_eu_member'))
                                    ->helperText(__('countries.tooltips.eu_member')),
                                Toggle::make('requires_vat')
                                    ->label(__('countries.fields.requires_vat'))
                                    ->helperText(__('countries.tooltips.requires_vat')),
                            ]),
                    ])
                    ->columns(2),
                Section::make(__('countries.sections.metadata'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('flag')
                                    ->label(__('countries.fields.flag'))
                                    ->maxLength(255),
                                TextInput::make('svg_flag')
                                    ->label(__('countries.fields.svg_flag'))
                                    ->maxLength(255),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('countries.fields.is_active'))
                                    ->default(true),
                                Toggle::make('is_enabled')
                                    ->label(__('countries.fields.is_enabled'))
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->label(__('countries.fields.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        KeyValue::make('metadata')
                            ->label(__('countries.fields.metadata'))
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText(__('countries.tooltips.metadata'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('countries.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('cca2')
                    ->label(__('countries.fields.cca2'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('region')
                    ->label(__('countries.fields.region'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('subregion')
                    ->label(__('countries.fields.subregion'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currency_code')
                    ->label(__('countries.fields.currency_code'))
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone_calling_code')
                    ->label(__('countries.fields.phone_calling_code'))
                    ->prefix('+')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_eu_member')
                    ->label(__('countries.fields.is_eu_member'))
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('requires_vat')
                    ->label(__('countries.fields.requires_vat'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_active')
                    ->label(__('countries.fields.is_active'))
                    ->getStateUsing(fn($record) => $record->is_active ? __('countries.statuses.active') : __('countries.statuses.inactive'))
                    ->colors([
                        'success' => fn($state) => $state === __('countries.statuses.active'),
                        'danger' => fn($state) => $state === __('countries.statuses.inactive'),
                    ])
                    ->toggleable(),
                TextColumn::make('cities_count')
                    ->label('Cities')
                    ->counts('cities')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('addresses_count')
                    ->label('Addresses')
                    ->counts('addresses')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('region')
                    ->label(__('countries.filters.region'))
                    ->options(fn() => Country::distinct()->pluck('region', 'region')->filter())
                    ->searchable(),
                SelectFilter::make('subregion')
                    ->label(__('countries.filters.subregion'))
                    ->options(fn() => Country::distinct()->pluck('subregion', 'subregion')->filter())
                    ->searchable(),
                TernaryFilter::make('is_eu_member')
                    ->label(__('countries.filters.eu_member'))
                    ->boolean(),
                TernaryFilter::make('requires_vat')
                    ->label(__('countries.filters.requires_vat'))
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label(__('countries.filters.is_active'))
                    ->boolean(),
                SelectFilter::make('currency_code')
                    ->label(__('countries.filters.currency_code'))
                    ->options(fn() => Country::distinct()->pluck('currency_code', 'currency_code')->filter())
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    TableViewAction::make(),
                    TableEditAction::make(),
                    Action::make('activate')
                        ->label(__('countries.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Country $record) {
                            $record->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('countries.notifications.activated'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Country $record) => !$record->is_active),
                    Action::make('deactivate')
                        ->label(__('countries.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Country $record) {
                            $record->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('countries.notifications.deactivated'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Country $record) => $record->is_active),
                ]),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableDeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('countries.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('countries.notifications.bulk_activated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('countries.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('countries.notifications.bulk_deactivated'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Handle getRelations functionality with proper error handling.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }

    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     *
     * @param  mixed  $record
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Code' => $record->cca2,
            'Region' => $record->region,
            'Currency' => $record->currency_code,
            'EU Member' => $record->is_eu_member ? 'Yes' : 'No',
        ];
    }

    /**
     * Get the global search result actions.
     */
    public static function getGlobalSearchResultActions($record): array
    {
        $actions = [];

        try {
            $actions[] = Action::make('view')
                ->label(__('countries.actions.view'))
                ->icon('heroicon-o-eye')
                ->url(self::getUrl('view', ['record' => $record]));
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        try {
            $actions[] = Action::make('edit')
                ->label(__('countries.actions.edit'))
                ->icon('heroicon-o-pencil')
                ->url(self::getUrl('edit', ['record' => $record]));
        } catch (\Exception $e) {
            // Route might not exist, skip this action
        }

        return $actions;
    }
}
