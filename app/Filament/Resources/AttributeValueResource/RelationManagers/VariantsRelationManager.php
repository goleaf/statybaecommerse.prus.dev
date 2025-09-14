<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeValueResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * VariantsRelationManager
 * 
 * Filament v4 resource for VariantsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $title = 'Product Variants';
    protected static ?string $modelLabel = 'Variant';
    protected static ?string $pluralModelLabel = 'Variants';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\Select::make('product_id')->label(__('products.product'))->relationship('product', 'name')->searchable()->preload()->required(), Forms\Components\TextInput::make('sku')->label(__('products.sku'))->maxLength(255), Forms\Components\TextInput::make('price')->label(__('products.price'))->numeric()->prefix('€')->required(), Forms\Components\TextInput::make('stock_quantity')->label(__('products.stock_quantity'))->numeric()->default(0), Forms\Components\Toggle::make('is_active')->label(__('products.active'))->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('sku')->columns([Tables\Columns\TextColumn::make('product.name')->label(__('products.product'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('sku')->label(__('products.sku'))->searchable()->sortable(), Tables\Columns\TextColumn::make('price')->label(__('products.price'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('stock_quantity')->label(__('products.stock_quantity'))->numeric()->sortable(), Tables\Columns\IconColumn::make('is_active')->label(__('products.active'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_active')->label(__('products.active'))->boolean()->native(false), Tables\Filters\SelectFilter::make('product_id')->label(__('products.product'))->relationship('product', 'name')->searchable()->preload()])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])])->defaultSort('sku', 'asc');
    }
}