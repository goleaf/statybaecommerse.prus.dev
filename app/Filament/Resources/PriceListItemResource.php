<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\PriceListItemResource\Pages;
use App\Filament\Resources\PriceListItemResource\Widgets;
use App\Models\PriceListItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
/**
 * PriceListItemResource
 * 
 * Filament v4 resource for PriceListItemResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PriceListItemResource extends Resource
{
    protected static ?string $model = PriceListItem::class;
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-currency-euro';
    /** @var BackedEnum|string|null */
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Pricing';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'display_name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.price_list_items.navigation_label');
    
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
        return __('admin.price_list_items.model_label');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.price_list_items.plural_model_label');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->schema([Tabs::make('Price List Item')->tabs([Tab::make(__('admin.price_list_items.tabs.basic_information'))->icon('heroicon-o-information-circle')->schema([Section::make(__('admin.price_list_items.sections.basic_information'))->schema([Grid::make(2)->schema([Select::make('price_list_id')->label(__('admin.price_list_items.fields.price_list'))->relationship('priceList', 'name')->required()->searchable()->preload()->createOptionForm([TextInput::make('name')->required()->maxLength(255), TextInput::make('code')->required()->maxLength(50)->unique('price_lists', 'code'), Select::make('currency_id')->relationship('currency', 'name')->required(), Toggle::make('is_enabled')->default(true)]), Select::make('product_id')->label(__('admin.price_list_items.fields.product'))->relationship('product', 'name')->searchable()->preload()->reactive()->afterStateUpdated(function ($state, callable $set) {
            if ($state) {
                $set('variant_id', null);
            }
        }), Select::make('variant_id')->label(__('admin.price_list_items.fields.variant'))->relationship(name: 'variant', titleAttribute: 'name', modifyQueryUsing: fn(Builder $query, callable $get) => $query->where('product_id', $get('product_id')))->searchable()->preload()->visible(fn(callable $get) => $get('product_id')), Toggle::make('is_active')->label(__('admin.price_list_items.fields.is_active'))->default(true)])])]), Tab::make(__('admin.price_list_items.tabs.pricing'))->icon('heroicon-o-currency-euro')->schema([Section::make(__('admin.price_list_items.sections.pricing'))->schema([Grid::make(3)->schema([TextInput::make('net_amount')->label(__('admin.price_list_items.fields.net_amount'))->required()->numeric()->step(0.01)->prefix('€')->minValue(0), TextInput::make('compare_amount')->label(__('admin.price_list_items.fields.compare_amount'))->numeric()->step(0.01)->prefix('€')->minValue(0)->helperText(__('admin.price_list_items.helpers.compare_amount')), TextInput::make('priority')->label(__('admin.price_list_items.fields.priority'))->numeric()->default(100)->minValue(1)->maxValue(999)->helperText(__('admin.price_list_items.helpers.priority'))])])]), Tab::make(__('admin.price_list_items.tabs.quantity_limits'))->icon('heroicon-o-cube')->schema([Section::make(__('admin.price_list_items.sections.quantity_limits'))->schema([Grid::make(2)->schema([TextInput::make('min_quantity')->label(__('admin.price_list_items.fields.min_quantity'))->numeric()->minValue(1)->helperText(__('admin.price_list_items.helpers.min_quantity')), TextInput::make('max_quantity')->label(__('admin.price_list_items.fields.max_quantity'))->numeric()->minValue(1)->helperText(__('admin.price_list_items.helpers.max_quantity'))])])]), Tab::make(__('admin.price_list_items.tabs.validity'))->icon('heroicon-o-calendar')->schema([Section::make(__('admin.price_list_items.sections.validity'))->schema([Grid::make(2)->schema([DateTimePicker::make('valid_from')->label(__('admin.price_list_items.fields.valid_from'))->displayFormat('d/m/Y H:i')->helperText(__('admin.price_list_items.helpers.valid_from')), DateTimePicker::make('valid_until')->label(__('admin.price_list_items.fields.valid_until'))->displayFormat('d/m/Y H:i')->helperText(__('admin.price_list_items.helpers.valid_until'))])])]), Tab::make(__('admin.price_list_items.tabs.translations'))->icon('heroicon-o-language')->schema([Section::make(__('admin.price_list_items.sections.translations'))->schema([Grid::make(1)->schema([TextInput::make('name')->label(__('admin.price_list_items.fields.name'))->maxLength(255)->helperText(__('admin.price_list_items.helpers.name')), Textarea::make('description')->label(__('admin.price_list_items.fields.description'))->rows(3)->maxLength(1000)->helperText(__('admin.price_list_items.helpers.description')), Textarea::make('notes')->label(__('admin.price_list_items.fields.notes'))->rows(3)->maxLength(1000)->helperText(__('admin.price_list_items.helpers.notes'))])])])])->columnSpanFull()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('priceList.name')->label(__('admin.price_list_items.fields.price_list'))->searchable()->sortable()->badge()->color('primary'), TextColumn::make('display_name')->label(__('admin.price_list_items.fields.display_name'))->searchable()->sortable()->weight('bold')->limit(50)->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            return strlen($state) > 50 ? $state : null;
        }), TextColumn::make('product.name')->label(__('admin.price_list_items.fields.product'))->searchable()->sortable()->placeholder(__('admin.price_list_items.no_product'))->toggleable(), TextColumn::make('variant.name')->label(__('admin.price_list_items.fields.variant'))->searchable()->sortable()->placeholder(__('admin.price_list_items.no_variant'))->toggleable(), TextColumn::make('net_amount')->label(__('admin.price_list_items.fields.net_amount'))->money('EUR')->sortable()->alignEnd()->color('success'), TextColumn::make('compare_amount')->label(__('admin.price_list_items.fields.compare_amount'))->money('EUR')->sortable()->alignEnd()->placeholder(__('admin.price_list_items.no_compare_price'))->toggleable(), BadgeColumn::make('discount_percentage')->label(__('admin.price_list_items.fields.discount_percentage'))->formatStateUsing(fn($state) => $state ? $state . '%' : '-')->color(fn($state) => $state > 20 ? 'success' : ($state > 10 ? 'warning' : 'gray'))->toggleable(), TextColumn::make('priority')->label(__('admin.price_list_items.fields.priority'))->sortable()->alignCenter()->badge()->color('gray'), IconColumn::make('is_active')->label(__('admin.price_list_items.fields.is_active'))->boolean()->sortable(), TextColumn::make('valid_from')->label(__('admin.price_list_items.fields.valid_from'))->dateTime('d/m/Y H:i')->sortable()->placeholder(__('admin.price_list_items.no_start_date'))->toggleable(isToggledHiddenByDefault: true), TextColumn::make('valid_until')->label(__('admin.price_list_items.fields.valid_until'))->dateTime('d/m/Y H:i')->sortable()->placeholder(__('admin.price_list_items.no_end_date'))->toggleable(isToggledHiddenByDefault: true), TextColumn::make('created_at')->label(__('admin.price_list_items.fields.created_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('price_list_id')->label(__('admin.price_list_items.filters.price_list'))->relationship('priceList', 'name')->preload(), SelectFilter::make('product_id')->label(__('admin.price_list_items.filters.product'))->relationship('product', 'name')->searchable()->preload(), TernaryFilter::make('is_active')->label(__('admin.price_list_items.filters.is_active'))->placeholder(__('admin.price_list_items.filters.all_items'))->trueLabel(__('admin.price_list_items.filters.active_only'))->falseLabel(__('admin.price_list_items.filters.inactive_only')), Filter::make('with_discount')->label(__('admin.price_list_items.filters.with_discount'))->query(fn(Builder $query): Builder => $query->whereNotNull('compare_amount')->whereColumn('compare_amount', '>', 'net_amount')), Filter::make('valid_now')->label(__('admin.price_list_items.filters.valid_now'))->query(fn(Builder $query): Builder => $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
        })), DateFilter::make('valid_from')->label(__('admin.price_list_items.filters.valid_from')), DateFilter::make('valid_until')->label(__('admin.price_list_items.filters.valid_until'))])->actions([ViewAction::make()->label(__('admin.actions.view')), EditAction::make()->label(__('admin.actions.edit')), DeleteAction::make()->label(__('admin.actions.delete'))])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()->label(__('admin.actions.delete_selected'))])])->defaultSort('priority', 'asc')->striped()->paginated([10, 25, 50, 100]);
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListPriceListItems::route('/'), 'create' => Pages\CreatePriceListItem::route('/create'), 'view' => Pages\ViewPriceListItem::route('/{record}'), 'edit' => Pages\EditPriceListItem::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\PriceListItemStatsWidget::class, Widgets\PriceListItemChartWidget::class];
    }
}