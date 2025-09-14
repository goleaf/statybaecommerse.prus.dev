<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeValueResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * ProductsRelationManager
 * 
 * Filament v4 resource for ProductsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $title = 'Products';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->label(__('products.name'))->required()->maxLength(255), Forms\Components\TextInput::make('sku')->label(__('products.sku'))->maxLength(255), Forms\Components\TextInput::make('price')->label(__('products.price'))->numeric()->prefix('€')->required(), Forms\Components\Toggle::make('is_active')->label(__('products.active'))->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('products.name'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('sku')->label(__('products.sku'))->searchable()->sortable(), Tables\Columns\TextColumn::make('price')->label(__('products.price'))->money('EUR')->sortable(), Tables\Columns\IconColumn::make('is_active')->label(__('products.active'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_active')->label(__('products.active'))->boolean()->native(false)])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])])->defaultSort('name', 'asc');
    }
}