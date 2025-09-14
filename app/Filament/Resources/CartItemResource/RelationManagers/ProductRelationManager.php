<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * ProductRelationManager
 * 
 * Filament v4 resource for ProductRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ProductRelationManager extends RelationManager
{
    protected static string $relationship = 'product';
    protected static ?string $title = 'admin.cart_items.relations.product';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->label(__('admin.products.fields.name'))->required()->maxLength(255), Forms\Components\TextInput::make('sku')->label(__('admin.products.fields.sku'))->required()->maxLength(255), Forms\Components\TextInput::make('price')->label(__('admin.products.fields.price'))->numeric()->required()->prefix('€')]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\ImageColumn::make('image')->label(__('admin.products.fields.image'))->getStateUsing(fn($record) => $record->getFirstMediaUrl('images', 'thumb'))->defaultImageUrl(asset('images/placeholder-product.png'))->circular()->size(40), Tables\Columns\TextColumn::make('name')->label(__('admin.products.fields.name'))->searchable()->sortable()->weight('medium'), Tables\Columns\TextColumn::make('sku')->label(__('admin.products.fields.sku'))->searchable()->copyable()->copyMessage(__('admin.common.copied'))->weight('mono'), Tables\Columns\TextColumn::make('price')->label(__('admin.products.fields.price'))->money('EUR')->sortable(), Tables\Columns\IconColumn::make('is_visible')->label(__('admin.products.fields.is_visible'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('admin.products.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_visible')->label(__('admin.products.filters.visible_only'))])->headerActions([Tables\Actions\CreateAction::make(), Tables\Actions\AttachAction::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}