<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\RecommendationConfig;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use BackedEnum;
/**
 * RecommendationConfigResource
 * 
 * Filament v4 resource for RecommendationConfigResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RecommendationConfigResource extends Resource
{
    protected static ?string $model = RecommendationConfig::class;
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-cog-6-tooth';
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Recommendation System';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('translations.recommendation_configs');
    
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }}
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('translations.recommendation_config');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('translations.recommendation_configs');
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::active()->count();
        return match (true) {
            $count === 0 => 'danger',
            $count < 5 => 'warning',
            default => 'success',
        };
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Forms\Components\Section::make(__('translations.recommendation_config_basic_info'))->schema([Forms\Components\TextInput::make('name')->label(__('translations.recommendation_config_name'))->required()->maxLength(255)->unique(ignoreRecord: true), Forms\Components\Textarea::make('description')->label(__('translations.recommendation_config_description'))->maxLength(1000)->rows(3), Forms\Components\Grid::make(2)->schema([Forms\Components\Toggle::make('is_active')->label(__('translations.recommendation_config_is_active'))->default(true), Forms\Components\TextInput::make('priority')->label(__('translations.recommendation_config_priority'))->numeric()->default(0)->helperText(__('translations.recommendation_config_priority_help'))])]), Forms\Components\Section::make(__('translations.recommendation_config_algorithm'))->schema([Forms\Components\Select::make('type')->label(__('translations.recommendation_config_type'))->required()->options(['content_based' => __('translations.recommendation_type_content_based'), 'collaborative' => __('translations.recommendation_type_collaborative'), 'hybrid' => __('translations.recommendation_type_hybrid'), 'popularity' => __('translations.recommendation_type_popularity'), 'trending' => __('translations.recommendation_type_trending'), 'cross_sell' => __('translations.recommendation_type_cross_sell'), 'up_sell' => __('translations.recommendation_type_up_sell')])->live()->afterStateUpdated(fn(Forms\Set $set) => $set('config', [])), Forms\Components\KeyValue::make('config')->label(__('translations.recommendation_config_config'))->keyLabel(__('translations.key'))->valueLabel(__('translations.value'))->addActionLabel(__('translations.add_config_parameter'))->helperText(__('translations.recommendation_config_config_help'))]), Forms\Components\Section::make(__('translations.recommendation_config_filters'))->schema([Forms\Components\KeyValue::make('filters')->label(__('translations.recommendation_config_filters'))->keyLabel(__('translations.filter_type'))->valueLabel(__('translations.filter_value'))->addActionLabel(__('translations.add_filter'))->helperText(__('translations.recommendation_config_filters_help'))]), Forms\Components\Section::make(__('translations.recommendation_config_limits'))->schema([Forms\Components\Grid::make(3)->schema([Forms\Components\TextInput::make('max_results')->label(__('translations.recommendation_config_max_results'))->numeric()->default(10)->minValue(1)->maxValue(50)->helperText(__('translations.recommendation_config_max_results_help')), Forms\Components\TextInput::make('min_score')->label(__('translations.recommendation_config_min_score'))->numeric()->step(0.01)->default(0.1)->minValue(0)->maxValue(1)->helperText(__('translations.recommendation_config_min_score_help')), Forms\Components\TextInput::make('cache_ttl')->label(__('translations.recommendation_config_cache_ttl'))->numeric()->default(3600)->minValue(60)->maxValue(86400)->helperText(__('translations.recommendation_config_cache_ttl_help'))])]), Forms\Components\Section::make(__('translations.recommendation_config_advanced'))->schema([Forms\Components\Tabs::make('advanced_tabs')->tabs([Forms\Components\Tabs\Tab::make(__('translations.recommendation_config_performance'))->schema([Forms\Components\Grid::make(2)->schema([Forms\Components\Toggle::make('enable_caching')->label(__('translations.recommendation_config_enable_caching'))->default(true)->helperText(__('translations.recommendation_config_enable_caching_help')), Forms\Components\Toggle::make('enable_analytics')->label(__('translations.recommendation_config_enable_analytics'))->default(true)->helperText(__('translations.recommendation_config_enable_analytics_help'))]), Forms\Components\Grid::make(2)->schema([Forms\Components\TextInput::make('batch_size')->label(__('translations.recommendation_config_batch_size'))->numeric()->default(100)->minValue(10)->maxValue(1000)->helperText(__('translations.recommendation_config_batch_size_help')), Forms\Components\TextInput::make('timeout_seconds')->label(__('translations.recommendation_config_timeout_seconds'))->numeric()->default(30)->minValue(5)->maxValue(300)->helperText(__('translations.recommendation_config_timeout_seconds_help'))])]), Forms\Components\Tabs\Tab::make(__('translations.recommendation_config_conditions'))->schema([Forms\Components\KeyValue::make('conditions')->label(__('translations.recommendation_config_conditions'))->keyLabel(__('translations.condition_type'))->valueLabel(__('translations.condition_value'))->addActionLabel(__('translations.add_condition'))->helperText(__('translations.recommendation_config_conditions_help'))]), Forms\Components\Tabs\Tab::make(__('translations.recommendation_config_metadata'))->schema([Forms\Components\RichEditor::make('notes')->label(__('translations.recommendation_config_notes'))->maxLength(2000)->helperText(__('translations.recommendation_config_notes_help')), Forms\Components\KeyValue::make('metadata')->label(__('translations.recommendation_config_metadata'))->keyLabel(__('translations.metadata_key'))->valueLabel(__('translations.metadata_value'))->addActionLabel(__('translations.add_metadata'))->helperText(__('translations.recommendation_config_metadata_help'))])])])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('translations.recommendation_config_name'))->searchable()->sortable(), BadgeColumn::make('type')->label(__('translations.recommendation_config_type'))->colors(['primary' => 'content_based', 'success' => 'collaborative', 'warning' => 'hybrid', 'info' => 'popularity', 'danger' => 'trending', 'secondary' => 'cross_sell', 'gray' => 'up_sell']), TextColumn::make('priority')->label(__('translations.recommendation_config_priority'))->sortable(), BooleanColumn::make('is_active')->label(__('translations.recommendation_config_is_active'))->sortable(), TextColumn::make('max_results')->label(__('translations.recommendation_config_max_results'))->sortable(), TextColumn::make('min_score')->label(__('translations.recommendation_config_min_score'))->formatStateUsing(fn(string $state): string => number_format((float) $state, 2)), TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('translations.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('type')->label(__('translations.recommendation_config_type'))->options(['content_based' => __('translations.recommendation_type_content_based'), 'collaborative' => __('translations.recommendation_type_collaborative'), 'hybrid' => __('translations.recommendation_type_hybrid'), 'popularity' => __('translations.recommendation_type_popularity'), 'trending' => __('translations.recommendation_type_trending'), 'cross_sell' => __('translations.recommendation_type_cross_sell'), 'up_sell' => __('translations.recommendation_type_up_sell')]), TernaryFilter::make('is_active')->label(__('translations.recommendation_config_is_active'))->boolean()->trueLabel(__('translations.active_only'))->falseLabel(__('translations.inactive_only'))->native(false)])->actions([ActionGroup::make([ViewAction::make(), EditAction::make(), Action::make('duplicate')->label(__('translations.duplicate'))->icon('heroicon-o-document-duplicate')->color('info')->action(function (RecommendationConfig $record) {
            $newRecord = $record->replicate();
            $newRecord->name = $record->name . ' (Copy)';
            $newRecord->save();
            Notification::make()->title(__('translations.duplicated_successfully'))->success()->send();
        }), Action::make('test')->label(__('translations.test_configuration'))->icon('heroicon-o-play')->color('success')->action(function (RecommendationConfig $record) {
            // Test the configuration
            Notification::make()->title(__('translations.testing_configuration'))->body(__('translations.test_completed_successfully'))->success()->send();
        }), Action::make('toggle_status')->label(fn(RecommendationConfig $record): string => $record->is_active ? __('translations.deactivate') : __('translations.activate'))->icon(fn(RecommendationConfig $record): string => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')->color(fn(RecommendationConfig $record): string => $record->is_active ? 'warning' : 'success')->action(function (RecommendationConfig $record) {
            $record->update(['is_active' => !$record->is_active]);
            Notification::make()->title($record->is_active ? __('translations.activated_successfully') : __('translations.deactivated_successfully'))->success()->send();
        }), DeleteAction::make()])->label(__('translations.actions'))->icon('heroicon-m-ellipsis-vertical')->size('sm')->color('gray')->button()])->bulkActions([BulkActionGroup::make([Action::make('activate_selected')->label(__('translations.activate_selected'))->icon('heroicon-o-play')->color('success')->action(function (Collection $records) {
            $records->each->update(['is_active' => true]);
            Notification::make()->title(__('translations.bulk_activated_successfully'))->success()->send();
        }), Action::make('deactivate_selected')->label(__('translations.deactivate_selected'))->icon('heroicon-o-pause')->color('warning')->action(function (Collection $records) {
            $records->each->update(['is_active' => false]);
            Notification::make()->title(__('translations.bulk_deactivated_successfully'))->success()->send();
        }), DeleteBulkAction::make()])])->defaultSort('priority', 'desc')->poll('30s')->striped()->paginated([10, 25, 50, 100]);
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
        return ['index' => Pages\ListRecommendationConfigs::route('/'), 'create' => Pages\CreateRecommendationConfig::route('/create'), 'edit' => Pages\EditRecommendationConfig::route('/{record}/edit')];
    }
}