<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * AttributesRelationManager
 * 
 * Filament v4 resource for AttributesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';
    protected static ?string $title = 'Attributes';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\Select::make('attribute_id')->label(__('translations.attribute'))->relationship('attributes', 'name')->searchable()->preload()->required(), Forms\Components\TextInput::make('value')->label(__('translations.attribute_value'))->required()->maxLength(255), Forms\Components\TextInput::make('sort_order')->label(__('translations.sort_order'))->numeric()->default(0)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('translations.attribute_name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('value')->label(__('translations.attribute_value'))->searchable()->sortable(), Tables\Columns\TextColumn::make('type')->label(__('translations.attribute_type'))->badge()->color(fn(string $state): string => match ($state) {
            'text' => 'gray',
            'number' => 'blue',
            'boolean' => 'green',
            'date' => 'yellow',
            'select' => 'purple',
            default => 'gray',
        }), Tables\Columns\IconColumn::make('is_required')->label(__('translations.is_required'))->boolean(), Tables\Columns\TextColumn::make('sort_order')->label(__('translations.sort_order'))->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\SelectFilter::make('type')->label(__('translations.attribute_type'))->options(['text' => __('translations.text'), 'number' => __('translations.number'), 'boolean' => __('translations.boolean'), 'date' => __('translations.date'), 'select' => __('translations.select')]), Tables\Filters\SelectFilter::make('is_required')->label(__('translations.is_required'))->options([true => __('translations.yes'), false => __('translations.no')])])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()->form(fn(Tables\Actions\AttachAction $action): array => [$action->getRecordSelect()->searchable()->preload(), Forms\Components\TextInput::make('value')->label(__('translations.attribute_value'))->required()->maxLength(255), Forms\Components\TextInput::make('sort_order')->label(__('translations.sort_order'))->numeric()->default(0)])])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])])->defaultSort('sort_order');
    }
}