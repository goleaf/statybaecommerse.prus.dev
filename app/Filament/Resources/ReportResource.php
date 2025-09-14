<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\Widgets;
use App\Models\Report;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\TimePicker;
use Filament\Schemas\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use BackedEnum;
/**
 * ReportResource
 * 
 * Filament v4 resource for ReportResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    // /**
    //  * @var UnitEnum|string|null
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Reports;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.analytics');
    }
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.reports');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.models.report');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.models.reports');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([TextInput::make('name')->label(__('admin.reports.fields.name'))->required()->maxLength(255), TextInput::make('slug')->label(__('admin.reports.fields.slug'))->required()->maxLength(255), Select::make('type')->label(__('admin.reports.fields.type'))->options(['sales' => __('admin.reports.types.sales'), 'products' => __('admin.reports.types.products'), 'customers' => __('admin.reports.types.customers')])->required(), Textarea::make('description')->label(__('admin.reports.fields.description'))->rows(3), Toggle::make('is_active')->label(__('admin.reports.fields.is_active'))->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('admin.reports.fields.name'))->searchable()->sortable(), TextColumn::make('type')->label(__('admin.reports.fields.type'))->badge()->color(fn(string $state): string => match ($state) {
            'sales' => 'success',
            'products' => 'info',
            'customers' => 'warning',
            'inventory' => 'secondary',
            'analytics' => 'gray',
        })->formatStateUsing(fn(string $state): string => __("admin.reports.types.{$state}")), TextColumn::make('category')->label(__('admin.reports.fields.category'))->badge()->color(fn(string $state): string => match ($state) {
            'sales' => 'success',
            'marketing' => 'info',
            'inventory' => 'secondary',
            'gray' => 'analytics',
        })->formatStateUsing(fn(string $state): string => __("admin.reports.categories.{$state}")), TextColumn::make('date_range')->label(__('admin.reports.fields.date_range'))->formatStateUsing(fn(?string $state): string => $state ? __("admin.reports.date_ranges.{$state}") : '-')->toggleable(), TextColumn::make('view_count')->label(__('admin.reports.fields.view_count'))->numeric()->sortable()->toggleable(), TextColumn::make('download_count')->label(__('admin.reports.fields.download_count'))->numeric()->sortable()->toggleable(), IconColumn::make('is_active')->label(__('admin.reports.fields.is_active'))->boolean()->sortable(), TextColumn::make('created_at')->label(__('admin.reports.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('admin.reports.fields.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('type')->label(__('admin.reports.fields.type'))->options(['sales' => __('admin.reports.types.sales'), 'products' => __('admin.reports.types.products'), 'customers' => __('admin.reports.types.customers'), 'inventory' => __('admin.reports.types.inventory'), 'analytics' => __('admin.reports.types.analytics'), 'financial' => __('admin.reports.types.financial'), 'marketing' => __('admin.reports.types.marketing'), 'custom' => __('admin.reports.types.custom')])->multiple(), SelectFilter::make('category')->label(__('admin.reports.fields.category'))->options(['sales' => __('admin.reports.categories.sales'), 'marketing' => __('admin.reports.categories.marketing'), 'inventory' => __('admin.reports.categories.inventory'), 'analytics' => __('admin.reports.categories.analytics')])->multiple(), TernaryFilter::make('is_active')->label(__('admin.reports.fields.is_active'))->boolean()->trueLabel(__('admin.common.active'))->falseLabel(__('admin.common.inactive'))->native(false)])->actions([ViewAction::make(), EditAction::make(), DeleteAction::make()])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])->defaultSort('created_at', 'desc');
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
        return ['index' => Pages\ListReports::route('/'), 'create' => Pages\CreateReport::route('/create'), 'edit' => Pages\EditReport::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([]);
    }
}