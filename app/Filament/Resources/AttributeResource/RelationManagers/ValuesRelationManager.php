<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
/**
 * ValuesRelationManager
 * 
 * Filament v4 resource for ValuesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';
    protected static ?string $title = 'Attribute Values';
    protected static ?string $modelLabel = 'Value';
    protected static ?string $pluralModelLabel = 'Values';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Section::make(__('attributes.value_information'))->schema([Forms\Components\TextInput::make('value')->label(__('attributes.value'))->required()->maxLength(255), Forms\Components\TextInput::make('display_value')->label(__('attributes.display_value'))->maxLength(255)->helperText(__('attributes.display_value_help')), Forms\Components\TextInput::make('sort_order')->label(__('attributes.sort_order'))->numeric()->default(0), Forms\Components\Toggle::make('is_enabled')->label(__('attributes.enabled'))->default(true), Forms\Components\ColorPicker::make('color')->label(__('attributes.color'))->helperText(__('attributes.color_help'))])->columns(2)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('value')->columns([Tables\Columns\TextColumn::make('value')->label(__('attributes.value'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('display_value')->label(__('attributes.display_value'))->searchable()->toggleable(), Tables\Columns\TextColumn::make('sort_order')->label(__('attributes.sort_order'))->numeric()->sortable(), Tables\Columns\IconColumn::make('is_enabled')->label(__('attributes.enabled'))->boolean(), Tables\Columns\TextColumn::make('products_count')->counts('products')->label(__('attributes.products_count'))->badge(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\Filter::make('enabled')->label(__('attributes.enabled_only'))->query(fn(Builder $query): Builder => $query->where('is_enabled', true)), Tables\Filters\TrashedFilter::make()])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make()])])->defaultSort('sort_order', 'asc');
    }
}