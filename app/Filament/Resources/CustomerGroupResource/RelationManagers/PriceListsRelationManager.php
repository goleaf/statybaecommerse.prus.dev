<?php

declare (strict_types=1);
namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * PriceListsRelationManager
 * 
 * Filament v4 resource for PriceListsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PriceListsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceLists';
    protected static ?string $title = 'customer_groups.relation_price_lists';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('currency')->maxLength(3)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('price_lists.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('currency')->label(__('price_lists.currency'))->sortable()->badge()->color('info'), Tables\Columns\TextColumn::make('is_default')->label(__('price_lists.is_default'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('price_lists.created_at'))->dateTime()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_default')->label(__('price_lists.is_default'))])->headerActions([Tables\Actions\AttachAction::make()->label(__('customer_groups.attach_price_list'))])->actions([Tables\Actions\DetachAction::make()->label(__('customer_groups.detach_price_list'))])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()->label(__('customer_groups.detach_selected'))])]);
    }
}