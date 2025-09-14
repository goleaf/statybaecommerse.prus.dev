<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationBlockResource\Pages;
use BackedEnum;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;
/**
 * RecommendationBlockResource
 * 
 * Filament v4 resource for RecommendationBlockResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RecommendationBlockResource extends Resource
{
    protected static ?string $model = RecommendationBlock::class;
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Recommendation System';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('translations.recommendation_block_basic_info'))->schema([TextInput::make('name')->label(__('translations.recommendation_block_name'))->required()->maxLength(255)->unique(ignoreRecord: true)->helperText(__('translations.recommendation_block_name_help')), TextInput::make('title')->label(__('translations.recommendation_block_title'))->required()->maxLength(255), Textarea::make('description')->label(__('translations.recommendation_block_description'))->maxLength(1000)->rows(3), Toggle::make('is_active')->label(__('translations.recommendation_block_is_active'))->default(true)]), Section::make(__('translations.recommendation_block_configuration'))->schema([Select::make('config_ids')->label(__('translations.recommendation_block_config_ids'))->multiple()->options(RecommendationConfig::active()->pluck('name', 'id'))->required()->helperText(__('translations.recommendation_block_config_ids_help')), Grid::make(2)->schema([TextInput::make('max_products')->label(__('translations.recommendation_block_max_products'))->numeric()->default(4)->minValue(1)->maxValue(20), TextInput::make('cache_duration')->label(__('translations.recommendation_block_cache_duration'))->numeric()->default(3600)->minValue(300)->suffix('seconds')->helperText(__('translations.recommendation_block_cache_duration_help'))])]), Section::make(__('translations.recommendation_block_display_settings'))->schema([KeyValue::make('display_settings')->label(__('translations.recommendation_block_display_settings'))->keyLabel(__('translations.setting_name'))->valueLabel(__('translations.setting_value'))->addActionLabel(__('translations.add_display_setting'))->helperText(__('translations.recommendation_block_display_settings_help'))])]);
    
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }}
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('translations.recommendation_block_name'))->searchable()->sortable(), TextColumn::make('title')->label(__('translations.recommendation_block_title'))->searchable()->sortable(), TextColumn::make('config_count')->label(__('translations.recommendation_block_config_count'))->getStateUsing(function (RecommendationBlock $record): int {
            return count($record->config_ids ?? []);
        }), TextColumn::make('max_products')->label(__('translations.recommendation_block_max_products'))->sortable(), TextColumn::make('cache_duration')->label(__('translations.recommendation_block_cache_duration'))->formatStateUsing(fn(int $state): string => gmdate('H:i:s', $state))->sortable(), BooleanColumn::make('is_active')->label(__('translations.recommendation_block_is_active'))->sortable(), TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('translations.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([TernaryFilter::make('is_active')->label(__('translations.recommendation_block_is_active'))->boolean()->trueLabel(__('translations.active_only'))->falseLabel(__('translations.inactive_only'))->native(false)])->actions([EditAction::make(), DeleteAction::make()])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])->defaultSort('name');
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
        return ['index' => Pages\ListRecommendationBlocks::route('/'), 'create' => Pages\CreateRecommendationBlock::route('/create'), 'edit' => Pages\EditRecommendationBlock::route('/{record}/edit')];
    }
}