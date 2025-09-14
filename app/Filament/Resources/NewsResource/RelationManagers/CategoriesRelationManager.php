<?php

declare (strict_types=1);
namespace App\Filament\Resources\NewsResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
/**
 * CategoriesRelationManager
 * 
 * Filament v4 resource for CategoriesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('name')->label(__('admin.news.categories.fields.name'))->required()->maxLength(255), Forms\Components\TextInput::make('slug')->label(__('admin.news.categories.fields.slug'))->required()->maxLength(255), Forms\Components\Textarea::make('description')->label(__('admin.news.categories.fields.description'))->maxLength(500)->rows(3), Forms\Components\Toggle::make('is_visible')->label(__('admin.news.categories.fields.is_visible'))->default(true), Forms\Components\Select::make('parent_id')->label(__('admin.news.categories.fields.parent_id'))->relationship('parent', 'name')->searchable()->preload(), Forms\Components\TextInput::make('sort_order')->label(__('admin.news.categories.fields.sort_order'))->numeric()->default(0)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('admin.news.categories.fields.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('slug')->label(__('admin.news.categories.fields.slug'))->searchable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('description')->label(__('admin.news.categories.fields.description'))->limit(50)->toggleable(isToggledHiddenByDefault: true), Tables\Columns\IconColumn::make('is_visible')->label(__('admin.news.categories.fields.is_visible'))->boolean(), Tables\Columns\TextColumn::make('parent.name')->label(__('admin.news.categories.fields.parent_id'))->sortable(), Tables\Columns\TextColumn::make('sort_order')->label(__('admin.news.categories.fields.sort_order'))->numeric()->sortable()])->filters([Tables\Filters\TernaryFilter::make('is_visible')->label(__('admin.news.categories.filters.is_visible')), Tables\Filters\Filter::make('has_parent')->label(__('admin.news.categories.filters.has_parent'))->query(fn(Builder $query): Builder => $query->whereNotNull('parent_id')), Tables\Filters\Filter::make('has_children')->label(__('admin.news.categories.filters.has_children'))->query(fn(Builder $query): Builder => $query->whereHas('children'))])->headerActions([Tables\Actions\CreateAction::make(), Tables\Actions\AttachAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}