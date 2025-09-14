<?php

declare (strict_types=1);
namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
    protected static ?string $title = 'admin.brands.relations.products_title';
    protected static ?string $modelLabel = 'admin.brands.relations.product_label';
    protected static ?string $pluralModelLabel = 'admin.brands.relations.products_label';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->label(__('admin.products.fields.name'))->required()->maxLength(255), Forms\Components\TextInput::make('sku')->label(__('admin.products.fields.sku'))->required()->maxLength(255)->unique(ignoreRecord: true), Forms\Components\Textarea::make('description')->label(__('admin.products.fields.description'))->maxLength(1000)->rows(3), Forms\Components\TextInput::make('price')->label(__('admin.products.fields.price'))->numeric()->prefix('€')->required(), Forms\Components\Toggle::make('is_enabled')->label(__('admin.products.fields.is_enabled'))->default(true)])->columns(2);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('admin.products.fields.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('sku')->label(__('admin.products.fields.sku'))->searchable()->sortable()->copyable(), Tables\Columns\TextColumn::make('price')->label(__('admin.products.fields.price'))->money('EUR')->sortable(), Tables\Columns\IconColumn::make('is_enabled')->label(__('admin.products.fields.is_enabled'))->boolean()->trueColor('success')->falseColor('danger'), Tables\Columns\TextColumn::make('created_at')->label(__('admin.products.fields.created_at'))->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TrashedFilter::make(), Tables\Filters\Filter::make('enabled')->label(__('admin.products.filters.enabled_only'))->query(fn(Builder $query): Builder => $query->where('is_enabled', true))->toggle()])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make()])])->modifyQueryUsing(fn(Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]))->defaultSort('name', 'asc');
    }
}