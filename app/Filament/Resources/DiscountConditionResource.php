<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Models\DiscountCondition;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
/**
 * DiscountConditionResource
 * 
 * Filament v4 resource for DiscountConditionResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
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
final class DiscountConditionResource extends Resource
{
    protected static ?string $model = DiscountCondition::class;
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-cog-6-tooth';
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Discounts';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Discount Conditions';
    protected static ?string $modelLabel = 'Discount Condition';
    protected static ?string $pluralModelLabel = 'Discount Conditions';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Forms\Components\Section::make(__('discount_conditions.sections.basic_info'))->schema([Forms\Components\Select::make('discount_id')->label(__('discount_conditions.fields.discount'))->relationship('discount', 'name')->searchable()->preload()->required()->createOptionForm([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('slug')->required()->maxLength(255), Forms\Components\Select::make('type')->options(['percentage' => __('discounts.types.percentage'), 'fixed' => __('discounts.types.fixed'), 'free_shipping' => __('discounts.types.free_shipping')])->required()]), Forms\Components\Select::make('type')->label(__('discount_conditions.fields.type'))->options(DiscountCondition::getTypes())->required()->live()->afterStateUpdated(fn(Forms\Set $set) => $set('operator', null)), Forms\Components\Select::make('operator')->label(__('discount_conditions.fields.operator'))->options(fn(Forms\Get $get) => DiscountCondition::getOperatorsForType($get('type')))->required()->live(), Forms\Components\TextInput::make('value')->label(__('discount_conditions.fields.value'))->required()->helperText(fn(Forms\Get $get) => self::getValueHelperText($get('type'), $get('operator'))), Forms\Components\TextInput::make('position')->label(__('discount_conditions.fields.position'))->numeric()->default(0)->helperText(__('discount_conditions.helpers.position')), Forms\Components\TextInput::make('priority')->label(__('discount_conditions.fields.priority'))->numeric()->default(0)->helperText(__('discount_conditions.helpers.priority')), Forms\Components\Toggle::make('is_active')->label(__('discount_conditions.fields.is_active'))->default(true)])->columns(2), Forms\Components\Section::make(__('discount_conditions.sections.translations'))->schema([Forms\Components\Repeater::make('translations')->label(__('discount_conditions.fields.translations'))->relationship('translations')->schema([Forms\Components\Select::make('locale')->label(__('discount_conditions.fields.locale'))->options(['lt' => __('common.locales.lt'), 'en' => __('common.locales.en')])->required(), Forms\Components\TextInput::make('name')->label(__('discount_conditions.fields.name'))->maxLength(255), Forms\Components\Textarea::make('description')->label(__('discount_conditions.fields.description'))->rows(3), Forms\Components\KeyValue::make('metadata')->label(__('discount_conditions.fields.metadata'))->keyLabel(__('discount_conditions.fields.metadata_key'))->valueLabel(__('discount_conditions.fields.metadata_value'))])->columns(2)->collapsible()->itemLabel(fn(array $state): ?string => $state['locale'] ?? null)]), Forms\Components\Section::make(__('discount_conditions.sections.advanced'))->schema([Forms\Components\KeyValue::make('metadata')->label(__('discount_conditions.fields.metadata'))->keyLabel(__('discount_conditions.fields.metadata_key'))->valueLabel(__('discount_conditions.fields.metadata_value'))->helperText(__('discount_conditions.helpers.metadata'))])->collapsible()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('discount.name')->label(__('discount_conditions.fields.discount'))->searchable()->sortable()->url(fn(DiscountCondition $record): string => route('filament.admin.resources.discounts.view', $record->discount)), Tables\Columns\TextColumn::make('type')->label(__('discount_conditions.fields.type'))->badge()->color(fn(string $state): string => match ($state) {
            'product', 'category', 'brand', 'collection' => 'info',
            'cart_total', 'item_qty' => 'warning',
            'zone', 'channel', 'currency' => 'success',
            'customer_group', 'user', 'partner_tier' => 'primary',
            'first_order', 'day_time' => 'secondary',
            'custom_script' => 'danger',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => DiscountCondition::getTypes()[$state] ?? $state), Tables\Columns\TextColumn::make('operator')->label(__('discount_conditions.fields.operator'))->badge()->color('gray')->formatStateUsing(fn(string $state): string => DiscountCondition::getOperators()[$state] ?? $state), Tables\Columns\TextColumn::make('value')->label(__('discount_conditions.fields.value'))->limit(50)->tooltip(function (Tables\Columns\TextColumn $column): ?string {
            $state = $column->getState();
            if (is_array($state)) {
                return implode(', ', $state);
            }
            return $state;
        }), Tables\Columns\TextColumn::make('position')->label(__('discount_conditions.fields.position'))->numeric()->sortable(), Tables\Columns\TextColumn::make('priority')->label(__('discount_conditions.fields.priority'))->numeric()->sortable()->badge()->color(fn(int $state): string => match (true) {
            $state <= 0 => 'danger',
            $state <= 5 => 'warning',
            $state <= 10 => 'success',
            default => 'gray',
        }), Tables\Columns\IconColumn::make('is_active')->label(__('discount_conditions.fields.is_active'))->boolean()->sortable(), Tables\Columns\TextColumn::make('human_readable_condition')->label(__('discount_conditions.fields.condition'))->limit(100)->tooltip(function (Tables\Columns\TextColumn $column): ?string {
            return $column->getState();
        }), Tables\Columns\TextColumn::make('created_at')->label(__('discount_conditions.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('updated_at')->label(__('discount_conditions.fields.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\SelectFilter::make('discount_id')->label(__('discount_conditions.fields.discount'))->relationship('discount', 'name')->searchable()->preload(), Tables\Filters\SelectFilter::make('type')->label(__('discount_conditions.fields.type'))->options(DiscountCondition::getTypes()), Tables\Filters\SelectFilter::make('operator')->label(__('discount_conditions.fields.operator'))->options(DiscountCondition::getOperators()), Tables\Filters\TernaryFilter::make('is_active')->label(__('discount_conditions.fields.is_active')), Tables\Filters\Filter::make('high_priority')->label(__('discount_conditions.filters.high_priority'))->query(fn(Builder $query): Builder => $query->where('priority', '>', 5)), Tables\Filters\Filter::make('low_priority')->label(__('discount_conditions.filters.low_priority'))->query(fn(Builder $query): Builder => $query->where('priority', '<=', 5)), Tables\Filters\Filter::make('numeric_conditions')->label(__('discount_conditions.filters.numeric_conditions'))->query(fn(Builder $query): Builder => $query->whereIn('type', ['cart_total', 'item_qty', 'priority'])), Tables\Filters\Filter::make('string_conditions')->label(__('discount_conditions.filters.string_conditions'))->query(fn(Builder $query): Builder => $query->whereIn('type', ['product', 'category', 'brand', 'collection', 'attribute_value']))])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\Action::make('test_condition')->label(__('discount_conditions.actions.test_condition'))->icon('heroicon-o-beaker')->color('info')->form([Forms\Components\TextInput::make('test_value')->label(__('discount_conditions.fields.test_value'))->required()->helperText(__('discount_conditions.helpers.test_value'))])->action(function (DiscountCondition $record, array $data): void {
            $matches = $record->matches($data['test_value']);
            $message = $matches ? __('discount_conditions.messages.condition_matches') : __('discount_conditions.messages.condition_does_not_match');
            \Filament\Notifications\Notification::make()->title($message)->success()->send();
        })])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), Tables\Actions\BulkAction::make('activate')->label(__('discount_conditions.actions.activate'))->icon('heroicon-o-check-circle')->color('success')->action(fn($records) => $records->each->update(['is_active' => true]))->requiresConfirmation(), Tables\Actions\BulkAction::make('deactivate')->label(__('discount_conditions.actions.deactivate'))->icon('heroicon-o-x-circle')->color('warning')->action(fn($records) => $records->each->update(['is_active' => false]))->requiresConfirmation(), Tables\Actions\BulkAction::make('set_priority')->label(__('discount_conditions.actions.set_priority'))->icon('heroicon-o-arrow-up')->color('info')->form([Forms\Components\TextInput::make('priority')->label(__('discount_conditions.fields.priority'))->numeric()->required()])->action(fn($records, array $data) => $records->each->update(['priority' => $data['priority']]))])])->defaultSort('priority', 'desc')->poll('30s');
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
        return ['index' => Pages\ListDiscountConditions::route('/'), 'create' => Pages\CreateDiscountCondition::route('/create'), 'view' => Pages\ViewDiscountCondition::route('/{record}'), 'edit' => Pages\EditDiscountCondition::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [DiscountConditionStatsWidget::class, DiscountConditionChartWidget::class, DiscountConditionTableWidget::class];
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return self::getModel()::count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
    /**
     * Handle getValueHelperText functionality with proper error handling.
     * @param string $type
     * @param string $operator
     * @return string
     */
    private static function getValueHelperText(string $type, string $operator): string
    {
        return match ($type) {
            'cart_total', 'item_qty' => __('discount_conditions.helpers.numeric_value'),
            'product', 'category', 'brand', 'collection' => __('discount_conditions.helpers.string_value'),
            'first_order', 'day_time' => __('discount_conditions.helpers.array_value'),
            'regex', 'not_regex' => __('discount_conditions.helpers.regex_value'),
            default => __('discount_conditions.helpers.general_value'),
        };
    }
}