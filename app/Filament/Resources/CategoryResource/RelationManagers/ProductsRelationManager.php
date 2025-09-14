<?php

declare (strict_types=1);
namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
    protected static ?string $title = 'Produktai';
    protected static ?string $modelLabel = 'Produktas';
    protected static ?string $pluralModelLabel = 'Produktai';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('sku')->required()->maxLength(255), Forms\Components\TextInput::make('price')->numeric()->prefix('€')->required(), Forms\Components\Toggle::make('is_visible')->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\ImageColumn::make('image')->circular()->size(40), Tables\Columns\TextColumn::make('name')->searchable()->sortable(), Tables\Columns\TextColumn::make('sku')->searchable()->sortable(), Tables\Columns\TextColumn::make('price')->money('EUR')->sortable(), Tables\Columns\IconColumn::make('is_visible')->boolean(), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([TernaryFilter::make('is_visible')->placeholder('Visi')->trueLabel('Matomi')->falseLabel('Nematomi'), SelectFilter::make('brand')->relationship('brand', 'name')])->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect(), Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make(), Tables\Actions\DeleteBulkAction::make()])])->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]));
    }
}