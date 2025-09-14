<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * CollectionsRelationManager
 * 
 * Filament v4 resource for CollectionsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';
    protected static ?string $title = 'Collections';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\Select::make('collection_id')->label(__('translations.collection'))->relationship('collections', 'name')->searchable()->preload()->required(), Forms\Components\TextInput::make('sort_order')->label(__('translations.sort_order'))->numeric()->default(0)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('translations.collection_name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('description')->label(__('translations.description'))->limit(50)->tooltip(function (Tables\Columns\TextColumn $column): ?string {
            $state = $column->getState();
            if (strlen($state) <= 50) {
                return null;
            }
            return $state;
        }), Tables\Columns\IconColumn::make('is_visible')->label(__('translations.is_visible'))->boolean(), Tables\Columns\TextColumn::make('sort_order')->label(__('translations.sort_order'))->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\SelectFilter::make('is_visible')->label(__('translations.is_visible'))->options([true => __('translations.yes'), false => __('translations.no')])])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()->form(fn(Tables\Actions\AttachAction $action): array => [$action->getRecordSelect()->searchable()->preload(), Forms\Components\TextInput::make('sort_order')->label(__('translations.sort_order'))->numeric()->default(0)])])->actions([Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}