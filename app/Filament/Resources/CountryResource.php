<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Filament\Resources\CountryResource\Widgets;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use App\Enums\NavigationGroup;
use UnitEnum;

final /**
 * CountryResource
 * 
 * Filament resource for admin panel management.
 */
class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.countries.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.countries.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.countries.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Country Information')
                    ->tabs([
                        Tab::make(__('admin.countries.sections.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('admin.countries.sections.basic_information'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('admin.countries.fields.name'))
                                                    ->helperText(__('admin.countries.helpers.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('name_official', $state)),

                                                TextInput::make('name_official')
                                                    ->label(__('admin.countries.fields.name_official'))
                                                    ->helperText(__('admin.countries.helpers.name_official'))
                                                    ->maxLength(255),
                                            ]),

                                        Textarea::make('description')
                                            ->label(__('admin.countries.fields.description'))
                                            ->helperText(__('admin.countries.helpers.description'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),

                                Section::make(__('admin.countries.sections.country_settings'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('cca2')
                                                    ->label(__('admin.countries.fields.cca2'))
                                                    ->helperText(__('admin.countries.helpers.cca2'))
                                                    ->required()
                                                    ->maxLength(2)
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true),

                                                TextInput::make('cca3')
                                                    ->label(__('admin.countries.fields.cca3'))
                                                    ->helperText(__('admin.countries.helpers.cca3'))
                                                    ->required()
                                                    ->maxLength(3)
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true),

                                                TextInput::make('ccn3')
                                                    ->label(__('admin.countries.fields.ccn3'))
                                                    ->helperText(__('admin.countries.helpers.ccn3'))
                                                    ->maxLength(3)
                                                    ->numeric()
                                                    ->unique(ignoreRecord: true),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('code')
                                                    ->label(__('admin.countries.fields.code'))
                                                    ->helperText(__('admin.countries.helpers.code'))
                                                    ->maxLength(10),

                                                TextInput::make('iso_code')
                                                    ->label(__('admin.countries.fields.iso_code'))
                                                    ->helperText(__('admin.countries.helpers.iso_code'))
                                                    ->maxLength(10),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make(__('admin.countries.sections.additional_information'))
                            ->icon('heroicon-o-map')
                                            ->schema([
                                Section::make('Location Information')
                                            ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('region')
                                                    ->label(__('admin.countries.fields.region'))
                                                    ->helperText(__('admin.countries.helpers.region'))
                                                    ->options([
                                                        'Europe' => 'Europe',
                                                        'Asia' => 'Asia',
                                                        'Africa' => 'Africa',
                                                        'North America' => 'North America',
                                                        'South America' => 'South America',
                                                        'Oceania' => 'Oceania',
                                                        'Antarctica' => 'Antarctica',
                                                    ])
                                                    ->searchable(),

                                                TextInput::make('subregion')
                                                    ->label(__('admin.countries.fields.subregion'))
                                                    ->helperText(__('admin.countries.helpers.subregion'))
                                                    ->maxLength(255),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('latitude')
                                                    ->label(__('admin.countries.fields.latitude'))
                                                    ->helperText(__('admin.countries.helpers.latitude'))
                                                    ->numeric()
                                                    ->step(0.000001)
                                                    ->minValue(-90)
                                                    ->maxValue(90),

                                                TextInput::make('longitude')
                                                    ->label(__('admin.countries.fields.longitude'))
                                                    ->helperText(__('admin.countries.helpers.longitude'))
                                                    ->numeric()
                                                    ->step(0.000001)
                                                    ->minValue(-180)
                                                    ->maxValue(180),
                                            ]),
                                    ])
                                    ->columns(1),

                                Section::make('Economic Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('currency_code')
                                                    ->label(__('admin.countries.fields.currency_code'))
                                                    ->helperText(__('admin.countries.helpers.currency_code'))
                                                    ->maxLength(3)
                                                    ->uppercase(),

                                                TextInput::make('currency_symbol')
                                                    ->label(__('admin.countries.fields.currency_symbol'))
                                                    ->helperText(__('admin.countries.helpers.currency_symbol'))
                                                    ->maxLength(10),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('phone_code')
                                                    ->label(__('admin.countries.fields.phone_code'))
                                                    ->helperText(__('admin.countries.helpers.phone_code'))
                                                    ->maxLength(10),

                                                TextInput::make('phone_calling_code')
                                                    ->label(__('admin.countries.fields.phone_calling_code'))
                                                    ->helperText(__('admin.countries.helpers.phone_calling_code'))
                                                    ->maxLength(10)
                                                    ->numeric(),
                                            ]),

                                        TextInput::make('timezone')
                                            ->label(__('admin.countries.fields.timezone'))
                                            ->helperText(__('admin.countries.helpers.timezone'))
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),

                                Section::make('Status & Settings')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label(__('admin.countries.fields.is_active'))
                                                    ->helperText(__('admin.countries.helpers.is_active'))
                                                    ->default(true),

                                                Toggle::make('is_enabled')
                                                    ->label(__('admin.countries.fields.is_enabled'))
                                                    ->helperText(__('admin.countries.helpers.is_enabled'))
                                                    ->default(true),

                                                Toggle::make('is_eu_member')
                                                    ->label(__('admin.countries.fields.is_eu_member'))
                                                    ->helperText(__('admin.countries.helpers.is_eu_member')),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('requires_vat')
                                                    ->label(__('admin.countries.fields.requires_vat'))
                                                    ->helperText(__('admin.countries.helpers.requires_vat')),

                                                TextInput::make('vat_rate')
                                            ->label(__('admin.countries.fields.vat_rate'))
                                                    ->helperText(__('admin.countries.helpers.vat_rate'))
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(100)
                                                    ->suffix('%'),
                                            ]),

                                        TextInput::make('sort_order')
                                            ->label(__('admin.countries.fields.sort_order'))
                                            ->helperText(__('admin.countries.helpers.sort_order'))
                                            ->numeric()
                                            ->default(0)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Media & Files')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Flag Files')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('flag')
                                                    ->label(__('admin.countries.fields.flag_file'))
                                                    ->helperText(__('admin.countries.helpers.flag_file'))
                                                    ->image()
                                                    ->directory('flags')
                                                    ->visibility('public')
                                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp']),

                                                FileUpload::make('svg_flag')
                                                    ->label(__('admin.countries.fields.svg_flag_file'))
                                                    ->helperText(__('admin.countries.helpers.svg_flag_file'))
                                                    ->directory('flags/svg')
                                                    ->visibility('public')
                                                    ->acceptedFileTypes(['image/svg+xml']),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Data Arrays')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Currencies')
                                    ->schema([
                                        Repeater::make('currencies')
                                            ->label(__('admin.countries.fields.currencies'))
                                            ->helperText(__('admin.countries.helpers.currencies'))
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label(__('admin.countries.helpers.currency_key'))
                                                    ->required()
                                                    ->maxLength(10),

                                                TextInput::make('value')
                                                    ->label(__('admin.countries.helpers.currency_value'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel(__('admin.countries.actions.add_currency'))
                                            ->collapsible(),
                                    ])
                                    ->columns(1),

                                Section::make('Languages')
                                    ->schema([
                                        Repeater::make('languages')
                                            ->label(__('admin.countries.fields.languages'))
                                            ->helperText(__('admin.countries.helpers.languages'))
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label(__('admin.countries.helpers.language_key'))
                                                    ->required()
                                                    ->maxLength(10),

                                                TextInput::make('value')
                                                    ->label(__('admin.countries.helpers.language_value'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel(__('admin.countries.actions.add_language'))
                                            ->collapsible(),
                                    ])
                                    ->columns(1),

                                Section::make('Timezones')
                                    ->schema([
                                        Repeater::make('timezones')
                                            ->label(__('admin.countries.fields.timezones'))
                                            ->helperText(__('admin.countries.helpers.timezones'))
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label(__('admin.countries.helpers.timezone_key'))
                                                    ->required()
                                                    ->maxLength(50),

                                                TextInput::make('value')
                                                    ->label(__('admin.countries.helpers.timezone_value'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel(__('admin.countries.actions.add_timezone'))
                                            ->collapsible(),
                                    ])
                                    ->columns(1),

                                Section::make('Metadata')
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label(__('admin.countries.fields.metadata'))
                                            ->helperText(__('admin.countries.helpers.metadata'))
                                            ->keyLabel(__('admin.countries.helpers.metadata_key'))
                                            ->valueLabel(__('admin.countries.helpers.metadata_value'))
                                            ->addActionLabel(__('admin.countries.actions.add_metadata')),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make(__('admin.countries.sections.translations'))
                            ->icon('heroicon-o-language')
                            ->schema([
                                Section::make('Translations')
                                    ->schema([
                                        Repeater::make('translations')
                                            ->relationship('translations')
                                            ->schema([
                                                Select::make('locale')
                                                    ->label('Language')
                                                    ->options([
                                                        'lt' => 'Lithuanian',
                                                        'en' => 'English',
                                                    ])
                                                    ->required(),

                                                TextInput::make('name')
                                                    ->label(__('admin.countries.fields.name'))
                                                    ->maxLength(255),

                                                TextInput::make('name_official')
                                                    ->label(__('admin.countries.fields.name_official'))
                                                    ->maxLength(255),

                                                Textarea::make('description')
                                                    ->label(__('admin.countries.fields.description'))
                                                    ->rows(3),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->defaultItems(0),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('flag')
                    ->label(__('admin.countries.fields.flag'))
                    ->disk('public')
                    ->visibility('public')
                    ->size(40)
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('admin.countries.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('cca2')
                    ->label(__('admin.countries.fields.cca2'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('cca3')
                    ->label(__('admin.countries.fields.cca3'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),

                TextColumn::make('region')
                    ->label(__('admin.countries.fields.region'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('currency_code')
                    ->label(__('admin.countries.fields.currency_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label(__('admin.countries.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_eu_member')
                    ->label(__('admin.countries.fields.is_eu_member'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray'),

                TextColumn::make('vat_rate')
                    ->label(__('admin.countries.fields.vat_rate'))
                    ->formatStateUsing(fn (?float $state): string => $state ? number_format($state, 2).'%' : 'N/A')
                    ->sortable(),

                TextColumn::make('addresses_count')
                    ->label(__('admin.countries.fields.addresses_count'))
                    ->counts('addresses')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.countries.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.countries.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('is_active')
                    ->label(__('admin.countries.filters.is_active'))
                    ->placeholder(__('admin.countries.filters.active_only'))
                    ->trueLabel(__('admin.countries.filters.active_only'))
                    ->falseLabel(__('admin.countries.filters.inactive_only')),

                TernaryFilter::make('is_eu_member')
                    ->label(__('admin.countries.filters.is_eu_member'))
                    ->placeholder(__('admin.countries.filters.eu_members_only'))
                    ->trueLabel(__('admin.countries.filters.eu_members_only'))
                    ->falseLabel(__('admin.countries.filters.non_eu_only')),

                TernaryFilter::make('requires_vat')
                    ->label(__('admin.countries.filters.requires_vat'))
                    ->placeholder(__('admin.countries.filters.vat_required'))
                    ->trueLabel(__('admin.countries.filters.vat_required'))
                    ->falseLabel(__('admin.countries.filters.no_vat')),

                SelectFilter::make('region')
                    ->label(__('admin.countries.filters.region'))
                    ->options([
                        'Europe' => 'Europe',
                        'Asia' => 'Asia',
                        'Africa' => 'Africa',
                        'North America' => 'North America',
                        'South America' => 'South America',
                        'Oceania' => 'Oceania',
                        'Antarctica' => 'Antarctica',
                    ])
                    ->multiple(),

                SelectFilter::make('currency_code')
                    ->label(__('admin.countries.filters.currency'))
                    ->options(fn (): array => Country::distinct()->pluck('currency_code', 'currency_code')->filter()->toArray())
                    ->multiple(),

                Filter::make('has_vat_rate')
                    ->label(__('admin.countries.filters.has_vat_rate'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('vat_rate')->where('vat_rate', '>', 0)),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),

                    BulkAction::make('activate')
                        ->label(__('admin.countries.actions.bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('deactivate')
                        ->label(__('admin.countries.actions.bulk_deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->emptyStateHeading(__('admin.countries.empty_state.heading'))
            ->emptyStateDescription(__('admin.countries.empty_state.description'))
            ->emptyStateIcon('heroicon-o-globe-alt');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\CitiesRelationManager::class,
            RelationManagers\RegionsRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\CustomersRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CountriesOverviewWidget::class,
            Widgets\CountriesStatsWidget::class,
            Widgets\CountriesByRegionWidget::class,
            Widgets\EuMembersWidget::class,
            Widgets\CountriesWithVatWidget::class,
            Widgets\CountryDetailsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name.' ('.$record->cca2.')';
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Region' => $record->region,
            'Currency' => $record->currency_code,
            'EU Member' => $record->is_eu_member ? 'Yes' : 'No',
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'name_official', 'cca2', 'cca3', 'region'];
    }
}
