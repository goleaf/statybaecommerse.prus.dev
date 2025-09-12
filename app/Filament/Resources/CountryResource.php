<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Filament\Resources\CountryResource\Widgets;
use App\Models\Country;
use UnitEnum;
use App\Services\MultiLanguageTabService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-globe-alt';

    /**
     * @var string|\BackedEnum|null
     */
    protected static UnitEnum|string|null $navigationGroup = 'shipping';

    protected static ?string $navigationLabel = 'admin.countries.navigation_label';

    protected static ?string $modelLabel = 'admin.countries.model_label';

    protected static ?string $pluralModelLabel = 'admin.countries.plural_model_label';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('CountryFormTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('admin.countries.sections.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make(__('admin.countries.sections.basic_information'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('cca2')
                                                    ->label(__('admin.countries.fields.cca2'))
                                                    ->required()
                                                    ->maxLength(2)
                                                    ->alpha()
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText(__('admin.countries.helpers.cca2')),
                                                Forms\Components\TextInput::make('cca3')
                                                    ->label(__('admin.countries.fields.cca3'))
                                                    ->required()
                                                    ->maxLength(3)
                                                    ->alpha()
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText(__('admin.countries.helpers.cca3')),
                                                Forms\Components\TextInput::make('ccn3')
                                                    ->label(__('admin.countries.fields.ccn3'))
                                                    ->maxLength(3)
                                                    ->numeric()
                                                    ->helperText(__('admin.countries.helpers.ccn3')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->label(__('admin.countries.fields.code'))
                                                    ->maxLength(3)
                                                    ->alpha()
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText(__('admin.countries.helpers.code')),
                                                Forms\Components\TextInput::make('iso_code')
                                                    ->label(__('admin.countries.fields.iso_code'))
                                                    ->maxLength(3)
                                                    ->alpha()
                                                    ->uppercase()
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText(__('admin.countries.helpers.iso_code')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('phone_code')
                                                    ->label(__('admin.countries.fields.phone_code'))
                                                    ->maxLength(10)
                                                    ->helperText(__('admin.countries.helpers.phone_code')),
                                                Forms\Components\TextInput::make('phone_calling_code')
                                                    ->label(__('admin.countries.fields.phone_calling_code'))
                                                    ->maxLength(10)
                                                    ->helperText(__('admin.countries.helpers.phone_calling_code')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('currency_code')
                                                    ->label(__('admin.countries.fields.currency_code'))
                                                    ->maxLength(3)
                                                    ->alpha()
                                                    ->uppercase()
                                                    ->helperText(__('admin.countries.helpers.currency_code')),
                                                Forms\Components\TextInput::make('currency_symbol')
                                                    ->label(__('admin.countries.fields.currency_symbol'))
                                                    ->maxLength(5)
                                                    ->helperText(__('admin.countries.helpers.currency_symbol')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('region')
                                                    ->label(__('admin.countries.fields.region'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.countries.helpers.region')),
                                                Forms\Components\TextInput::make('subregion')
                                                    ->label(__('admin.countries.fields.subregion'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.countries.helpers.subregion')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('latitude')
                                                    ->label(__('admin.countries.fields.latitude'))
                                                    ->numeric()
                                                    ->step(0.000001)
                                                    ->minValue(-90)
                                                    ->maxValue(90)
                                                    ->helperText(__('admin.countries.helpers.latitude')),
                                                Forms\Components\TextInput::make('longitude')
                                                    ->label(__('admin.countries.fields.longitude'))
                                                    ->numeric()
                                                    ->step(0.000001)
                                                    ->minValue(-180)
                                                    ->maxValue(180)
                                                    ->helperText(__('admin.countries.helpers.longitude')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('timezone')
                                                    ->label(__('admin.countries.fields.timezone'))
                                                    ->maxLength(50)
                                                    ->helperText(__('admin.countries.helpers.timezone')),
                                                Forms\Components\TextInput::make('sort_order')
                                                    ->label(__('admin.countries.fields.sort_order'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->helperText(__('admin.countries.helpers.sort_order')),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('admin.countries.sections.country_settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make(__('admin.countries.sections.country_settings'))
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('is_active')
                                                    ->label(__('admin.countries.fields.is_active'))
                                                    ->default(true)
                                                    ->helperText(__('admin.countries.helpers.is_active')),
                                                Forms\Components\Toggle::make('is_enabled')
                                                    ->label(__('admin.countries.fields.is_enabled'))
                                                    ->default(true)
                                                    ->helperText(__('admin.countries.helpers.is_enabled')),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('is_eu_member')
                                                    ->label(__('admin.countries.fields.is_eu_member'))
                                                    ->default(false)
                                                    ->helperText(__('admin.countries.helpers.is_eu_member')),
                                                Forms\Components\Toggle::make('requires_vat')
                                                    ->label(__('admin.countries.fields.requires_vat'))
                                                    ->default(false)
                                                    ->helperText(__('admin.countries.helpers.requires_vat')),
                                            ]),
                                        Forms\Components\TextInput::make('vat_rate')
                                            ->label(__('admin.countries.fields.vat_rate'))
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->helperText(__('admin.countries.helpers.vat_rate')),
                                        Forms\Components\KeyValue::make('currencies')
                                            ->label(__('admin.countries.fields.currencies'))
                                            ->keyLabel(__('admin.countries.helpers.currency_key'))
                                            ->valueLabel(__('admin.countries.helpers.currency_value'))
                                            ->addActionLabel(__('admin.countries.actions.add_currency'))
                                            ->helperText(__('admin.countries.helpers.currencies')),
                                        Forms\Components\KeyValue::make('languages')
                                            ->label(__('admin.countries.fields.languages'))
                                            ->keyLabel(__('admin.countries.helpers.language_key'))
                                            ->valueLabel(__('admin.countries.helpers.language_value'))
                                            ->addActionLabel(__('admin.countries.actions.add_language'))
                                            ->helperText(__('admin.countries.helpers.languages')),
                                        Forms\Components\KeyValue::make('timezones')
                                            ->label(__('admin.countries.fields.timezones'))
                                            ->keyLabel(__('admin.countries.helpers.timezone_key'))
                                            ->valueLabel(__('admin.countries.helpers.timezone_value'))
                                            ->addActionLabel(__('admin.countries.actions.add_timezone'))
                                            ->helperText(__('admin.countries.helpers.timezones')),
                                        Forms\Components\KeyValue::make('metadata')
                                            ->label(__('admin.countries.fields.metadata'))
                                            ->keyLabel(__('admin.countries.helpers.metadata_key'))
                                            ->valueLabel(__('admin.countries.helpers.metadata_value'))
                                            ->addActionLabel(__('admin.countries.actions.add_metadata'))
                                            ->helperText(__('admin.countries.helpers.metadata')),
                                    ])
                                    ->columns(1),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('admin.countries.sections.additional_information'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Section::make(__('admin.countries.sections.additional_information'))
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('flag')
                                                    ->label(__('admin.countries.fields.flag'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.countries.helpers.flag')),
                                                Forms\Components\TextInput::make('svg_flag')
                                                    ->label(__('admin.countries.fields.svg_flag'))
                                                    ->maxLength(255)
                                                    ->helperText(__('admin.countries.helpers.svg_flag')),
                                            ]),
                                        Forms\Components\FileUpload::make('flag_file')
                                            ->label(__('admin.countries.fields.flag_file'))
                                            ->image()
                                            ->directory('flags')
                                            ->visibility('public')
                                            ->helperText(__('admin.countries.helpers.flag_file')),
                                        Forms\Components\FileUpload::make('svg_flag_file')
                                            ->label(__('admin.countries.fields.svg_flag_file'))
                                            ->acceptedFileTypes(['image/svg+xml'])
                                            ->directory('flags/svg')
                                            ->visibility('public')
                                            ->helperText(__('admin.countries.helpers.svg_flag_file')),
                                    ])
                                    ->columns(1),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('admin.countries.sections.translations'))
                            ->icon('heroicon-o-language')
                            ->schema([
                                ...MultiLanguageTabService::createSectionedTabs([
                                    'name' => [
                                        'type' => 'text',
                                        'label' => __('admin.countries.fields.name'),
                                        'required' => true,
                                        'maxLength' => 255,
                                        'helper' => __('admin.countries.helpers.name'),
                                    ],
                                    'name_official' => [
                                        'type' => 'text',
                                        'label' => __('admin.countries.fields.name_official'),
                                        'required' => false,
                                        'maxLength' => 255,
                                        'helper' => __('admin.countries.helpers.name_official'),
                                    ],
                                    'description' => [
                                        'type' => 'textarea',
                                        'label' => __('admin.countries.fields.description'),
                                        'required' => false,
                                        'rows' => 4,
                                        'helper' => __('admin.countries.helpers.description'),
                                    ],
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('flag')
                    ->label(__('admin.countries.fields.flag'))
                    ->disk('public')
                    ->height(30)
                    ->width(45)
                    ->circular(false)
                    ->defaultImageUrl(asset('images/no-flag.png')),
                Tables\Columns\TextColumn::make('translated_name')
                    ->label(__('admin.countries.fields.name'))
                    ->getStateUsing(fn(Country $record): string => $record->trans('name') ?: $record->name ?: '-')
                    ->searchable(['name'])
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('cca2')
                    ->label(__('admin.countries.fields.cca2'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('cca3')
                    ->label(__('admin.countries.fields.cca3'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('region')
                    ->label(__('admin.countries.fields.region'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subregion')
                    ->label(__('admin.countries.fields.subregion'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label(__('admin.countries.fields.currency_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('phone_calling_code')
                    ->label(__('admin.countries.fields.phone_calling_code'))
                    ->formatStateUsing(fn(?string $state): string => $state ? "+{$state}" : '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.countries.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('is_eu_member')
                    ->label(__('admin.countries.fields.is_eu_member'))
                    ->boolean()
                    ->trueIcon('heroicon-o-flag')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('vat_rate')
                    ->label(__('admin.countries.fields.vat_rate'))
                    ->formatStateUsing(fn(?float $state): string => $state ? number_format($state, 2) . '%' : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('addresses_count')
                    ->label(__('admin.countries.fields.addresses_count'))
                    ->counts('addresses')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.countries.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.countries.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.countries.filters.is_active'))
                    ->trueLabel(__('admin.countries.filters.active_only'))
                    ->falseLabel(__('admin.countries.filters.inactive_only')),
                Tables\Filters\TernaryFilter::make('is_eu_member')
                    ->label(__('admin.countries.filters.is_eu_member'))
                    ->trueLabel(__('admin.countries.filters.eu_members_only'))
                    ->falseLabel(__('admin.countries.filters.non_eu_only')),
                Tables\Filters\TernaryFilter::make('requires_vat')
                    ->label(__('admin.countries.filters.requires_vat'))
                    ->trueLabel(__('admin.countries.filters.vat_required'))
                    ->falseLabel(__('admin.countries.filters.no_vat')),
                Tables\Filters\SelectFilter::make('region')
                    ->label(__('admin.countries.filters.region'))
                    ->options(fn() => Country::distinct()->pluck('region', 'region')->filter())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('currency_code')
                    ->label(__('admin.countries.filters.currency'))
                    ->options(fn() => Country::distinct()->pluck('currency_code', 'currency_code')->filter())
                    ->searchable(),
                Tables\Filters\Filter::make('has_vat_rate')
                    ->label(__('admin.countries.filters.has_vat_rate'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('vat_rate')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.countries.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.countries.actions.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.countries.actions.delete'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.countries.confirmations.delete_heading'))
                    ->modalDescription(__('admin.countries.confirmations.delete_description')),
                Tables\Actions\RestoreAction::make()
                    ->label(__('admin.countries.actions.restore')),
                Tables\Actions\ForceDeleteAction::make()
                    ->label(__('admin.countries.actions.force_delete'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.countries.confirmations.force_delete_heading'))
                    ->modalDescription(__('admin.countries.confirmations.force_delete_description')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.countries.actions.bulk_delete'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.countries.confirmations.bulk_delete_heading'))
                        ->modalDescription(__('admin.countries.confirmations.bulk_delete_description')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label(__('admin.countries.actions.bulk_restore')),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label(__('admin.countries.actions.bulk_force_delete'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.countries.confirmations.bulk_force_delete_heading'))
                        ->modalDescription(__('admin.countries.confirmations.bulk_force_delete_description')),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.countries.actions.bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.countries.confirmations.bulk_activate_heading')),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.countries.actions.bulk_deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.countries.confirmations.bulk_deactivate_heading')),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->emptyStateHeading(__('admin.countries.empty_state.heading'))
            ->emptyStateDescription(__('admin.countries.empty_state.description'))
            ->emptyStateIcon('heroicon-o-globe-alt');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\RegionsRelationManager::class,
            RelationManagers\CitiesRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\CustomersRelationManager::class,
            RelationManagers\ShippingZonesRelationManager::class,
            RelationManagers\TaxRatesRelationManager::class,
            RelationManagers\CurrenciesRelationManager::class,
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

    public static function getWidgets(): array
    {
        return [
            Widgets\CountriesOverviewWidget::class,
            Widgets\CountriesByRegionWidget::class,
            Widgets\EuMembersWidget::class,
            Widgets\CountriesWithVatWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
