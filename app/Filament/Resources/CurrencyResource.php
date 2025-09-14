<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use BackedEnum;
use App\Models\Currency;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\NavigationGroup;
use UnitEnum;
/**
 * CurrencyResource
 * 
 * Filament v4 resource for CurrencyResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property int|null $navigationSort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 1;
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('currency_title');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('currency_single');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('currency_title');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('currency_sections.basic_information'))->description(__('currency_help.code'))->icon('heroicon-o-currency-dollar')->schema([Grid::make(2)->schema([TextInput::make('name')->label(__('currency_name'))->required()->maxLength(255)->translatable()->helperText(__('currency_help.code')), TextInput::make('code')->label(__('currency_code'))->required()->maxLength(3)->unique(ignoreRecord: true)->helperText(__('currency_help.code'))->placeholder('EUR')->rules(['regex:/^[A-Z]{3}$/'])]), Grid::make(2)->schema([TextInput::make('symbol')->label(__('currency_symbol'))->maxLength(10)->helperText(__('currency_help.symbol'))->placeholder('€'), TextInput::make('decimal_places')->label(__('currency_decimal_places'))->numeric()->required()->default(2)->minValue(0)->maxValue(4)->helperText(__('currency_help.decimal_places'))])]), Section::make(__('currency_sections.settings'))->description(__('currency_help.exchange_rate'))->icon('heroicon-o-arrow-trending-up')->schema([TextInput::make('exchange_rate')->label(__('currency_exchange_rate'))->numeric()->required()->default(1)->step(1.0E-6)->helperText(__('currency_help.exchange_rate'))]), Section::make(__('currency_sections.settings'))->description(__('currency_help.is_default'))->icon('heroicon-o-cog-6-tooth')->schema([Grid::make(2)->schema([Toggle::make('is_enabled')->label(__('currency_is_enabled'))->default(true)->helperText(__('currency_help.is_default')), Toggle::make('is_default')->label(__('currency_is_default'))->default(false)->helperText(__('currency_help.is_default'))])])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('currency_name'))->searchable()->sortable()->translatable(), TextColumn::make('code')->label(__('currency_code'))->searchable()->sortable()->badge()->color('primary'), TextColumn::make('symbol')->label(__('currency_symbol'))->searchable()->formatStateUsing(fn(string $state): string => $state ?: '-'), TextColumn::make('exchange_rate')->label(__('currency_exchange_rate'))->numeric(decimalPlaces: 6)->sortable()->alignEnd(), TextColumn::make('decimal_places')->label(__('currency_decimal_places'))->numeric()->sortable()->alignCenter(), TextColumn::make('zones_count')->label(__('currency_zones_count'))->counts('zones')->sortable()->alignCenter()->badge()->color('info'), TextColumn::make('prices_count')->label(__('currency_prices_count'))->counts('prices')->sortable()->alignCenter()->badge()->color('success'), IconColumn::make('is_enabled')->label(__('currency_is_enabled'))->boolean()->sortable(), IconColumn::make('is_default')->label(__('currency_is_default'))->boolean()->sortable()->color(fn(bool $state): string => $state ? 'warning' : 'gray'), TextColumn::make('created_at')->label(__('currency_created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('currency_updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([TernaryFilter::make('is_enabled')->label(__('currency_filters.is_enabled'))->placeholder(__('admin.common.all'))->trueLabel(__('admin.common.enabled'))->falseLabel(__('admin.common.disabled')), TernaryFilter::make('is_default')->label(__('currency_filters.is_default'))->placeholder(__('admin.common.all'))->trueLabel(__('currency_is_default'))->falseLabel(__('admin.common.not_default')), Filter::make('has_zones')->label(__('currency_filters.has_zones'))->query(fn(Builder $query): Builder => $query->whereHas('zones')), Filter::make('has_prices')->label(__('currency_filters.has_prices'))->query(fn(Builder $query): Builder => $query->whereHas('prices')), Filter::make('exchange_rate_range')->label(__('currency_filters.exchange_rate_range'))->form([TextInput::make('exchange_rate_from')->label(__('admin.common.from'))->numeric()->step(1.0E-6), TextInput::make('exchange_rate_until')->label(__('admin.common.until'))->numeric()->step(1.0E-6)])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['exchange_rate_from'], fn(Builder $query, $rate): Builder => $query->where('exchange_rate', '>=', $rate))->when($data['exchange_rate_until'], fn(Builder $query, $rate): Builder => $query->where('exchange_rate', '<=', $rate));
        }), Filter::make('decimal_places_range')->label(__('currency_filters.decimal_places_range'))->form([Select::make('decimal_places_from')->label(__('admin.common.from'))->options([0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4']), Select::make('decimal_places_until')->label(__('admin.common.until'))->options([0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4'])])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['decimal_places_from'], fn(Builder $query, $places): Builder => $query->where('decimal_places', '>=', $places))->when($data['decimal_places_until'], fn(Builder $query, $places): Builder => $query->where('decimal_places', '<=', $places));
        }), DateFilter::make('created_at')->label(__('currency_filters.created_from'))])->actions([Action::make('set_default')->label(__('currency_actions.set_default'))->icon('heroicon-o-star')->color('warning')->requiresConfirmation()->modalHeading(__('currency_actions.set_default'))->modalDescription(__('currency_help.is_default'))->action(function (Currency $record): void {
            // Remove default from all currencies
            Currency::where('is_default', true)->update(['is_default' => false]);
            // Set this currency as default
            $record->update(['is_default' => true]);
            // Show notification
            \Filament\Notifications\Notification::make()->title(__('currency_notifications.set_default'))->body(__('currency_notifications.set_default_description', ['name' => $record->name]))->success()->send();
        })->visible(fn(Currency $record): bool => !$record->is_default), Action::make('enable')->label(__('currency_actions.enable'))->icon('heroicon-o-check-circle')->color('success')->action(function (Currency $record): void {
            $record->update(['is_enabled' => true]);
            \Filament\Notifications\Notification::make()->title(__('currency_notifications.updated'))->body(__('currency_notifications.updated_description', ['name' => $record->name]))->success()->send();
        })->visible(fn(Currency $record): bool => !$record->is_enabled), Action::make('disable')->label(__('currency_actions.disable'))->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()->modalHeading(__('currency_actions.disable'))->modalDescription(__('currency_help.is_default'))->action(function (Currency $record): void {
            $record->update(['is_enabled' => false]);
            \Filament\Notifications\Notification::make()->title(__('currency_notifications.updated'))->body(__('currency_notifications.updated_description', ['name' => $record->name]))->success()->send();
        })->visible(fn(Currency $record): bool => $record->is_enabled), ViewAction::make(), EditAction::make(), DeleteAction::make()])->bulkActions([BulkActionGroup::make([BulkAction::make('enable')->label(__('currency_actions.enable'))->icon('heroicon-o-check-circle')->color('success')->action(function (Collection $records): void {
            $records->each->update(['is_enabled' => true]);
            \Filament\Notifications\Notification::make()->title(__('currency_notifications.updated'))->body(__('currency_notifications.updated_description', ['name' => 'Selected currencies']))->success()->send();
        }), BulkAction::make('disable')->label(__('currency_actions.disable'))->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()->action(function (Collection $records): void {
            $records->each->update(['is_enabled' => false]);
            \Filament\Notifications\Notification::make()->title(__('currency_notifications.updated'))->body(__('currency_notifications.updated_description', ['name' => 'Selected currencies']))->success()->send();
        }), DeleteBulkAction::make()])])->defaultSort('is_default', 'desc')->poll('30s');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [\App\Filament\Resources\CurrencyResource\RelationManagers\ZonesRelationManager::class, \App\Filament\Resources\CurrencyResource\RelationManagers\PricesRelationManager::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListCurrencies::route('/'), 'create' => Pages\CreateCurrency::route('/create'), 'view' => Pages\ViewCurrency::route('/{record}'), 'edit' => Pages\EditCurrency::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [\App\Filament\Widgets\CurrencyOverviewWidget::class, \App\Filament\Widgets\CurrencyExchangeRatesWidget::class, \App\Filament\Widgets\CurrencyUsageWidget::class];
    }
}