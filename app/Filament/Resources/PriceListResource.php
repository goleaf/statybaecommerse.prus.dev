<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use BackedEnum;
use App\Filament\Resources\PriceListResource\RelationManagers;
use App\Filament\Resources\PriceListResource\Widgets;
use App\Models\PriceList;
use Filament\Forms;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
/**
 * PriceListResource
 * 
 * Filament v4 resource for PriceListResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-currency-dollar';
    /** @var BackedEnum|string|null */
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Pricing Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.price_lists.navigation_label');
    
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }}
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.price_lists.model_label');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.price_lists.plural_model_label');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('admin.price_lists.basic_information'))->schema([Grid::make(2)->schema([TextInput::make('name')->label(__('admin.price_lists.fields.name'))->required()->maxLength(255)->live()->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('code', \Str::slug($state)) : null), TextInput::make('code')->label(__('admin.price_lists.fields.code'))->required()->maxLength(255)->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9_-]+$/']), Select::make('currency_id')->label(__('admin.price_lists.fields.currency'))->relationship('currency', 'name')->required()->searchable()->preload()->createOptionForm([TextInput::make('name')->required()->maxLength(255), TextInput::make('code')->required()->maxLength(3)->rules(['regex:/^[A-Z]{3}$/']), TextInput::make('symbol')->required()->maxLength(10), TextInput::make('exchange_rate')->required()->numeric()->default(1)]), Select::make('zone_id')->label(__('admin.price_lists.fields.zone'))->relationship('zone', 'name')->searchable()->preload()]), Textarea::make('description')->label(__('admin.price_lists.fields.description'))->rows(3)]), Section::make(__('admin.price_lists.settings'))->schema([Grid::make(3)->schema([Toggle::make('is_enabled')->label(__('admin.price_lists.fields.is_enabled'))->default(true), Toggle::make('is_default')->label(__('admin.price_lists.fields.is_default'))->default(false), Toggle::make('auto_apply')->label(__('admin.price_lists.fields.auto_apply'))->default(false), TextInput::make('priority')->label(__('admin.price_lists.fields.priority'))->numeric()->default(100)->minValue(1)->maxValue(999), TextInput::make('min_order_amount')->label(__('admin.price_lists.fields.min_order_amount'))->numeric()->prefix('€')->step(0.01), TextInput::make('max_order_amount')->label(__('admin.price_lists.fields.max_order_amount'))->numeric()->prefix('€')->step(0.01)])]), Section::make(__('admin.price_lists.validity_period'))->schema([Grid::make(2)->schema([DateTimePicker::make('starts_at')->label(__('admin.price_lists.fields.starts_at'))->displayFormat('d/m/Y H:i'), DateTimePicker::make('ends_at')->label(__('admin.price_lists.fields.ends_at'))->displayFormat('d/m/Y H:i')->after('starts_at')])]), Section::make(__('admin.price_lists.associations'))->schema([Select::make('customerGroups')->label(__('admin.price_lists.fields.customer_groups'))->relationship('customerGroups', 'name')->multiple()->searchable()->preload(), Select::make('partners')->label(__('admin.price_lists.fields.partners'))->relationship('partners', 'name')->multiple()->searchable()->preload()]), Section::make(__('admin.price_lists.metadata'))->schema([KeyValue::make('metadata')->label(__('admin.price_lists.fields.metadata'))->keyLabel(__('admin.price_lists.fields.metadata_key'))->valueLabel(__('admin.price_lists.fields.metadata_value'))])->collapsible()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('admin.price_lists.fields.name'))->searchable()->sortable(), TextColumn::make('code')->label(__('admin.price_lists.fields.code'))->searchable()->sortable()->badge()->color('gray'), TextColumn::make('currency.name')->label(__('admin.price_lists.fields.currency'))->searchable()->sortable(), TextColumn::make('zone.name')->label(__('admin.price_lists.fields.zone'))->searchable()->sortable(), TextColumn::make('priority')->label(__('admin.price_lists.fields.priority'))->numeric()->sortable()->alignEnd(), IconColumn::make('is_enabled')->label(__('admin.price_lists.fields.is_enabled'))->boolean(), IconColumn::make('is_default')->label(__('admin.price_lists.fields.is_default'))->boolean(), IconColumn::make('auto_apply')->label(__('admin.price_lists.fields.auto_apply'))->boolean(), TextColumn::make('items_count')->label(__('admin.price_lists.fields.items_count'))->counts('items')->numeric()->alignEnd(), TextColumn::make('customer_groups_count')->label(__('admin.price_lists.fields.customer_groups_count'))->counts('customerGroups')->numeric()->alignEnd(), TextColumn::make('partners_count')->label(__('admin.price_lists.fields.partners_count'))->counts('partners')->numeric()->alignEnd(), TextColumn::make('starts_at')->label(__('admin.price_lists.fields.starts_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('ends_at')->label(__('admin.price_lists.fields.ends_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('created_at')->label(__('admin.price_lists.fields.created_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('currency_id')->label(__('admin.price_lists.fields.currency'))->relationship('currency', 'name')->searchable()->preload(), SelectFilter::make('zone_id')->label(__('admin.price_lists.fields.zone'))->relationship('zone', 'name')->searchable()->preload(), TernaryFilter::make('is_enabled')->label(__('admin.price_lists.fields.is_enabled')), TernaryFilter::make('is_default')->label(__('admin.price_lists.fields.is_default')), TernaryFilter::make('auto_apply')->label(__('admin.price_lists.fields.auto_apply')), DateFilter::make('starts_at')->label(__('admin.price_lists.fields.starts_at')), DateFilter::make('ends_at')->label(__('admin.price_lists.fields.ends_at'))])->actions([ViewAction::make()->label(__('admin.actions.view')), EditAction::make()->label(__('admin.actions.edit')), DeleteAction::make()->label(__('admin.actions.delete'))])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()->label(__('admin.actions.delete_selected'))])])->defaultSort('priority', 'asc')->striped()->paginated([10, 25, 50]);
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [RelationManagers\ItemsRelationManager::class, RelationManagers\CustomerGroupsRelationManager::class, RelationManagers\PartnersRelationManager::class];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\PriceListStatsWidget::class, Widgets\PriceListActivityWidget::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListPriceLists::route('/'), 'create' => Pages\CreatePriceList::route('/create'), 'view' => Pages\ViewPriceList::route('/{record}'), 'edit' => Pages\EditPriceList::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}