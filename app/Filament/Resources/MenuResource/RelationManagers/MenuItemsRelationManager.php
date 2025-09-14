<?php

declare (strict_types=1);
namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Models\MenuItem;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Hidden;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
/**
 * MenuItemsRelationManager
 * 
 * Filament v4 resource for MenuItemsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $recordTitleAttribute
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';
    protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $title = 'Menu Items';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->schema([Grid::make(2)->schema([TextInput::make('label')->label(__('admin.menu_items.label'))->required()->maxLength(255), TextInput::make('url')->label(__('admin.menu_items.url'))->maxLength(255)->helperText(__('admin.menu_items.url_helper'))]), Grid::make(2)->schema([TextInput::make('route_name')->label(__('admin.menu_items.route_name'))->maxLength(255)->helperText(__('admin.menu_items.route_name_helper')), TextInput::make('icon')->label(__('admin.menu_items.icon'))->maxLength(255)->helperText(__('admin.menu_items.icon_helper'))]), Grid::make(2)->schema([Select::make('parent_id')->label(__('admin.menu_items.parent'))->options(function (RelationManager $livewire): array {
            return MenuItem::where('menu_id', $livewire->ownerRecord->id)->where('id', '!=', $livewire->mountedTableActionRecord?->id)->pluck('label', 'id')->toArray();
        })->searchable()->preload()->helperText(__('admin.menu_items.parent_helper')), TextInput::make('sort_order')->label(__('admin.menu_items.sort_order'))->numeric()->default(0)->helperText(__('admin.menu_items.sort_order_helper'))]), Toggle::make('is_visible')->label(__('admin.menu_items.is_visible'))->default(true), Hidden::make('menu_id')->default(fn(RelationManager $livewire): int => $livewire->ownerRecord->id)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('label')->columns([TextColumn::make('label')->label(__('admin.menu_items.label'))->searchable()->sortable()->weight('medium'), TextColumn::make('url')->label(__('admin.menu_items.url'))->limit(30)->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            return strlen($state) > 30 ? $state : null;
        }), TextColumn::make('route_name')->label(__('admin.menu_items.route_name'))->badge()->color('info'), TextColumn::make('parent.label')->label(__('admin.menu_items.parent'))->badge()->color('gray')->placeholder(__('admin.menu_items.no_parent')), TextColumn::make('sort_order')->label(__('admin.menu_items.sort_order'))->sortable()->badge()->color('primary'), IconColumn::make('is_visible')->label(__('admin.menu_items.is_visible'))->boolean()->sortable()])->filters([])->headerActions([CreateAction::make()->mutateFormDataUsing(function (array $data): array {
            $data['menu_id'] = $this->ownerRecord->id;
            return $data;
        })])->actions([ActionGroup::make([EditAction::make(), DeleteAction::make()])])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->defaultSort('sort_order', 'asc')->reorderable('sort_order');
    }
}