<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\RelationManagers;
use App\Filament\Resources\CityResource\Widgets;
use App\Models\City;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
/**
 * CityResource
 * 
 * Filament v4 resource for CityResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $navigationLabel
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CityResource extends Resource
{
    protected static ?string $model = City::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('cities.navigation_label');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('cities.model_label');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('cities.plural_model_label');
    }
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('cities.navigation_group');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Forms\Components\Section::make(__('cities.basic_information'))->schema([Forms\Components\TextInput::make('name')->label(__('cities.name'))->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(function (string $context, $state, Forms\Set $set) {
            if ($context === 'create') {
                $set('slug', \Str::slug($state));
            }
        }), Forms\Components\TextInput::make('slug')->label(__('cities.slug'))->required()->maxLength(255)->unique(ignoreRecord: true)->rules(['alpha_dash']), Forms\Components\TextInput::make('code')->label(__('cities.code'))->required()->maxLength(10)->unique(ignoreRecord: true)->rules(['alpha_dash']), Forms\Components\Textarea::make('description')->label(__('cities.description'))->rows(3)->maxLength(1000)])->columns(2), Forms\Components\Section::make(__('cities.location'))->schema([Forms\Components\Select::make('country_id')->label(__('cities.country'))->relationship('country', 'name')->searchable()->preload()->required()->live()->afterStateUpdated(function (Forms\Set $set) {
            $set('zone_id', null);
        }), Forms\Components\Select::make('zone_id')->label(__('cities.zone'))->relationship('zone', 'name', function (Builder $query, Forms\Get $get) {
            return $query->when($get('country_id'), function (Builder $query, $countryId) {
                $query->whereHas('countries', function (Builder $query) use ($countryId) {
                    $query->where('country_id', $countryId);
                });
            });
        })->searchable()->preload()->live()->afterStateUpdated(function (Forms\Set $set) {
        }), Forms\Components\Select::make('parent_id')->label(__('cities.parent_city'))->relationship('parent', 'name', function (Builder $query, Forms\Get $get) {
            return $query->when($get('country_id'), function (Builder $query, $countryId) {
                $query->where('country_id', $countryId);
            });
        })->searchable()->preload(), Forms\Components\Select::make('level')->label(__('cities.level'))->options([0 => __('cities.level_city'), 1 => __('cities.level_district'), 2 => __('cities.level_neighborhood'), 3 => __('cities.level_suburb')])->default(0)->required()->helperText(__('cities.level_help'))])->columns(2), Forms\Components\Section::make(__('cities.geographic_data'))->schema([Forms\Components\TextInput::make('latitude')->label(__('cities.latitude'))->numeric()->step(1.0E-8)->suffix('°')->minValue(-90)->maxValue(90), Forms\Components\TextInput::make('longitude')->label(__('cities.longitude'))->numeric()->step(1.0E-8)->suffix('°')->minValue(-180)->maxValue(180), Forms\Components\TextInput::make('population')->label(__('cities.population'))->numeric()->minValue(0)->maxValue(999999999), Forms\Components\TagsInput::make('postal_codes')->label(__('cities.postal_codes'))->placeholder(__('cities.postal_codes_placeholder'))->separator(',')])->columns(2), Forms\Components\Section::make(__('cities.additional_data'))->schema([Forms\Components\TextInput::make('type')->label(__('cities.type'))->maxLength(255), Forms\Components\TextInput::make('area')->label(__('cities.area'))->numeric()->step(0.01)->suffix(' km²'), Forms\Components\TextInput::make('density')->label(__('cities.density'))->numeric()->step(0.01)->suffix(' /km²'), Forms\Components\TextInput::make('elevation')->label(__('cities.elevation'))->numeric()->step(0.01)->suffix(' m'), Forms\Components\TextInput::make('timezone')->label(__('cities.timezone'))->maxLength(255), Forms\Components\TextInput::make('currency_code')->label(__('cities.currency_code'))->maxLength(3), Forms\Components\TextInput::make('currency_symbol')->label(__('cities.currency_symbol'))->maxLength(5), Forms\Components\TextInput::make('language_code')->label(__('cities.language_code'))->maxLength(5), Forms\Components\TextInput::make('language_name')->label(__('cities.language_name'))->maxLength(255), Forms\Components\TextInput::make('phone_code')->label(__('cities.phone_code'))->maxLength(10), Forms\Components\TextInput::make('postal_code')->label(__('cities.postal_code'))->maxLength(20)])->columns(3)->collapsible(), Forms\Components\Section::make(__('cities.status'))->schema([Forms\Components\Toggle::make('is_enabled')->label(__('cities.is_enabled'))->default(true), Forms\Components\Toggle::make('is_default')->label(__('cities.is_default'))->default(false), Forms\Components\Toggle::make('is_capital')->label(__('cities.is_capital'))->default(false), Forms\Components\TextInput::make('sort_order')->label(__('cities.sort_order'))->numeric()->default(0)->minValue(0)])->columns(2), Forms\Components\Section::make(__('cities.translations'))->schema([Forms\Components\Repeater::make('translations')->label(__('cities.translations'))->relationship('translations')->schema([Forms\Components\Select::make('locale')->label(__('cities.locale'))->options(['lt' => __('cities.locale_lt'), 'en' => __('cities.locale_en'), 'de' => __('cities.locale_de'), 'ru' => __('cities.locale_ru')])->required(), Forms\Components\TextInput::make('name')->label(__('cities.name'))->required()->maxLength(255), Forms\Components\Textarea::make('description')->label(__('cities.description'))->rows(2)->maxLength(1000)])->columns(3)->addActionLabel(__('cities.add_translation'))->collapsible()->itemLabel(fn(array $state): ?string => $state['locale'] ?? null)]), Forms\Components\Section::make(__('cities.metadata'))->schema([Forms\Components\KeyValue::make('metadata')->label(__('cities.metadata'))->keyLabel(__('cities.key'))->valueLabel(__('cities.value'))])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('code')->label(__('cities.code'))->searchable()->sortable()->badge()->color('primary'), Tables\Columns\TextColumn::make('name')->label(__('cities.name'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('country.name')->label(__('cities.country'))->sortable()->toggleable(),  Tables\Columns\TextColumn::make('zone.name')->label(__('cities.zone'))->sortable()->toggleable(), Tables\Columns\TextColumn::make('parent.name')->label(__('cities.parent_city'))->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('level')->label(__('cities.level'))->sortable()->formatStateUsing(fn(int $state): string => match ($state) {
            0 => __('cities.level_city'),
            1 => __('cities.level_district'),
            2 => __('cities.level_neighborhood'),
            3 => __('cities.level_suburb'),
            default => __('cities.level_city'),
        })->badge()->color(fn(int $state): string => match ($state) {
            0 => 'success',
            1 => 'info',
            2 => 'warning',
            3 => 'gray',
            default => 'gray',
        }), Tables\Columns\TextColumn::make('population')->label(__('cities.population'))->numeric()->sortable()->formatStateUsing(fn(?int $state): string => $state ? number_format($state) : '-')->toggleable(), Tables\Columns\TextColumn::make('postal_codes')->label(__('cities.postal_codes'))->badge()->separator(',')->toggleable(isToggledHiddenByDefault: true), Tables\Columns\IconColumn::make('is_capital')->label(__('cities.is_capital'))->boolean()->trueIcon('heroicon-o-crown')->falseIcon('heroicon-o-building-office')->trueColor('warning')->falseColor('gray'), Tables\Columns\IconColumn::make('is_enabled')->label(__('cities.is_enabled'))->boolean()->trueColor('success')->falseColor('danger'), Tables\Columns\IconColumn::make('is_default')->label(__('cities.is_default'))->boolean()->trueColor('primary')->falseColor('gray'), Tables\Columns\TextColumn::make('sort_order')->label(__('cities.sort_order'))->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('area')->label(__('cities.area'))->numeric()->formatStateUsing(fn(?float $state): string => $state ? number_format($state, 2) . ' km²' : '-')->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('density')->label(__('cities.density'))->numeric()->formatStateUsing(fn(?float $state): string => $state ? number_format($state, 2) . ' /km²' : '-')->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('timezone')->label(__('cities.timezone'))->searchable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('currency_code')->label(__('cities.currency_code'))->badge()->color('info')->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('language_code')->label(__('cities.language_code'))->badge()->color('warning')->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('created_at')->label(__('cities.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('updated_at')->label(__('cities.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_enabled')->label(__('cities.is_enabled'))->placeholder(__('cities.filter_all'))->trueLabel(__('cities.filter_enabled'))->falseLabel(__('cities.filter_disabled')), Tables\Filters\TernaryFilter::make('is_capital')->label(__('cities.is_capital'))->placeholder(__('cities.filter_all'))->trueLabel(__('cities.filter_capital'))->falseLabel(__('cities.filter_non_capital')), Tables\Filters\TernaryFilter::make('is_default')->label(__('cities.is_default'))->placeholder(__('cities.filter_all'))->trueLabel(__('cities.filter_default'))->falseLabel(__('cities.filter_non_default')), Tables\Filters\SelectFilter::make('country')->label(__('cities.country'))->relationship('country', 'name')->searchable()->preload(), Tables\Filters\SelectFilter::make('region')->label(__('cities.region'))->relationship('region', 'name')->searchable()->preload(), Tables\Filters\SelectFilter::make('zone')->label(__('cities.zone'))->relationship('zone', 'name')->searchable()->preload(), Tables\Filters\SelectFilter::make('level')->label(__('cities.level'))->options([0 => __('cities.level_city'), 1 => __('cities.level_district'), 2 => __('cities.level_neighborhood'), 3 => __('cities.level_suburb')]), Tables\Filters\Filter::make('with_coordinates')->label(__('cities.with_coordinates'))->query(fn(Builder $query): Builder => $query->whereNotNull('latitude')->whereNotNull('longitude')), Tables\Filters\Filter::make('with_population')->label(__('cities.with_population'))->query(fn(Builder $query): Builder => $query->where('population', '>', 0)), Tables\Filters\Filter::make('population_range')->form([Forms\Components\TextInput::make('population_from')->label(__('cities.population_from'))->numeric()->placeholder('0'), Forms\Components\TextInput::make('population_to')->label(__('cities.population_to'))->numeric()->placeholder('1000000')])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['population_from'], fn(Builder $query, $from): Builder => $query->where('population', '>=', $from))->when($data['population_to'], fn(Builder $query, $to): Builder => $query->where('population', '<=', $to));
        }), Tables\Filters\SelectFilter::make('type')->label(__('cities.type'))->options(fn(): array => City::distinct('type')->pluck('type', 'type')->toArray())->searchable(), Tables\Filters\SelectFilter::make('timezone')->label(__('cities.timezone'))->options(fn(): array => City::distinct('timezone')->pluck('timezone', 'timezone')->toArray())->searchable(), Tables\Filters\SelectFilter::make('currency_code')->label(__('cities.currency_code'))->options(fn(): array => City::distinct('currency_code')->pluck('currency_code', 'currency_code')->toArray())->searchable(), Tables\Filters\TrashedFilter::make()])->actions([Tables\Actions\ViewAction::make()->label(__('cities.view')), Tables\Actions\EditAction::make()->label(__('cities.edit')), Tables\Actions\DeleteAction::make()->label(__('cities.delete')), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()->label(__('cities.bulk_delete')), Tables\Actions\RestoreBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make()])])->defaultSort('sort_order')->poll('30s');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [RelationManagers\AddressesRelationManager::class, RelationManagers\ChildrenRelationManager::class, RelationManagers\TranslationsRelationManager::class];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\CityStatsOverview::class, Widgets\CityPopulationChart::class, Widgets\CitiesByCountryChart::class, Widgets\RecentCitiesTable::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListCities::route('/'), 'create' => Pages\CreateCity::route('/create'), 'view' => Pages\ViewCity::route('/{record}'), 'edit' => Pages\EditCity::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->with(['country', 'zone', 'parent', 'children']);
    }
    /**
     * Handle getGlobalSearchResultTitle functionality with proper error handling.
     * @param mixed $record
     * @return string
     */
    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name . ' (' . $record->code . ')';
    }
    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     * @param mixed $record
     * @return array
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [__('cities.country') => $record->country?->name, __('cities.level') => match ($record->level) {
            0 => __('cities.level_city'),
            1 => __('cities.level_district'),
            2 => __('cities.level_neighborhood'),
            3 => __('cities.level_suburb'),
            default => __('cities.level_city'),
        }];
    }
    /**
     * Handle getGlobalSearchResultActions functionality with proper error handling.
     * @param mixed $record
     * @return array
     */
    public static function getGlobalSearchResultActions($record): array
    {
        return [Tables\Actions\Action::make('view')->label(__('cities.view'))->url(static::getUrl('view', ['record' => $record]))->icon('heroicon-o-eye'), Tables\Actions\Action::make('edit')->label(__('cities.edit'))->url(static::getUrl('edit', ['record' => $record]))->icon('heroicon-o-pencil')];
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        return match (true) {
            $count === 0 => 'danger',
            $count < 10 => 'warning',
            default => 'success',
        };
    }
}