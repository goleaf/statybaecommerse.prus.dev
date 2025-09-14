<?php

declare (strict_types=1);
namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
    protected static ?string $title = 'Products Using This Attribute';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->label(__('products.name'))->required()->maxLength(255), Forms\Components\TextInput::make('slug')->label(__('products.slug'))->required()->maxLength(255), Forms\Components\Textarea::make('description')->label(__('products.description'))->rows(3), Forms\Components\TextInput::make('price')->label(__('products.price'))->numeric()->prefix('€')->required(), Forms\Components\TextInput::make('sku')->label(__('products.sku'))->maxLength(255), Forms\Components\Toggle::make('is_enabled')->label(__('products.enabled'))->default(true), Forms\Components\Toggle::make('is_featured')->label(__('products.featured'))->default(false)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('products.name'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('slug')->label(__('products.slug'))->searchable()->toggleable()->copyable(), Tables\Columns\TextColumn::make('price')->label(__('products.price'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('sku')->label(__('products.sku'))->searchable()->toggleable(), Tables\Columns\IconColumn::make('is_enabled')->label(__('products.enabled'))->boolean()->toggleable(), Tables\Columns\IconColumn::make('is_featured')->label(__('products.featured'))->boolean()->toggleable(), Tables\Columns\TextColumn::make('stock_quantity')->label(__('products.stock_quantity'))->numeric()->sortable()->toggleable(), Tables\Columns\TextColumn::make('created_at')->label(__('translations.created_at'))->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\Filter::make('enabled')->label(__('products.enabled_only'))->query(fn(Builder $query): Builder => $query->where('is_enabled', true)), Tables\Filters\Filter::make('featured')->label(__('products.featured_only'))->query(fn(Builder $query): Builder => $query->where('is_featured', true)), Tables\Filters\Filter::make('in_stock')->label(__('products.in_stock_only'))->query(fn(Builder $query): Builder => $query->where('stock_quantity', '>', 0)), Tables\Filters\TrashedFilter::make()])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make(), Tables\Actions\DeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make()])])->defaultSort('name', 'asc');
    }
}