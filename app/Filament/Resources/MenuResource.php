<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use BackedEnum;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
/**
 * MenuResource
 * 
 * Filament v4 resource for MenuResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property string|null $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @property string|null $navigationLabel
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Menus';
    protected static ?string $modelLabel = 'Menu';
    protected static ?string $pluralModelLabel = 'Menus';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('admin.menus.basic_information'))->schema([Grid::make(2)->schema([TextInput::make('key')->label(__('admin.menus.key'))->required()->unique(ignoreRecord: true)->maxLength(255)->helperText(__('admin.menus.key_helper')), TextInput::make('name')->label(__('admin.menus.name'))->required()->maxLength(255)->helperText(__('admin.menus.name_helper'))]), Select::make('location')->label(__('admin.menus.location'))->required()->options(['header' => __('admin.menus.locations.header'), 'footer' => __('admin.menus.locations.footer'), 'sidebar' => __('admin.menus.locations.sidebar'), 'mobile' => __('admin.menus.locations.mobile')])->helperText(__('admin.menus.location_helper')), Toggle::make('is_active')->label(__('admin.menus.is_active'))->default(true)->helperText(__('admin.menus.is_active_helper'))])->columns(1)]);
    
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Content->label();
    }}
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('key')->label(__('admin.menus.key'))->searchable()->sortable()->copyable(), TextColumn::make('name')->label(__('admin.menus.name'))->searchable()->sortable()->weight('medium'), TextColumn::make('location')->label(__('admin.menus.location'))->badge()->color(fn(string $state): string => match ($state) {
            'header' => 'primary',
            'footer' => 'gray',
            'sidebar' => 'success',
            'mobile' => 'warning',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => __("admin.menus.locations.{$state}")), TextColumn::make('items_count')->label(__('admin.menus.items_count'))->counts('allItems')->badge()->color('info'), IconColumn::make('is_active')->label(__('admin.menus.is_active'))->boolean()->sortable(), TextColumn::make('created_at')->label(__('admin.common.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('admin.common.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Filter::make('is_active')->label(__('admin.menus.is_active'))->query(fn(Builder $query): Builder => $query->where('is_active', true)), Filter::make('location')->label(__('admin.menus.location'))->form([Select::make('location')->label(__('admin.menus.location'))->options(['header' => __('admin.menus.locations.header'), 'footer' => __('admin.menus.locations.footer'), 'sidebar' => __('admin.menus.locations.sidebar'), 'mobile' => __('admin.menus.locations.mobile')])->multiple()])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['location'], fn(Builder $query, $location): Builder => $query->whereIn('location', $location));
        })])->actions([ActionGroup::make([ViewAction::make(), EditAction::make(), DeleteAction::make()])])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])->defaultSort('created_at', 'desc');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [RelationManagers\MenuItemsRelationManager::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListMenus::route('/'), 'create' => Pages\CreateMenu::route('/create'), 'view' => Pages\ViewMenu::route('/{record}'), 'edit' => Pages\EditMenu::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
    /**
     * Handle getGlobalSearchEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['allItems']);
    }
    /**
     * Handle getGloballySearchableAttributes functionality with proper error handling.
     * @return array
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['key', 'name', 'location'];
    }
    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     * @param mixed $record
     * @return array
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [__('admin.menus.location') => __("admin.menus.locations.{$record->location}"), __('admin.menus.items_count') => $record->allItems()->count()];
    }
}