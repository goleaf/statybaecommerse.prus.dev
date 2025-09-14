<?php

declare (strict_types=1);
namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * UsersRelationManager
 * 
 * Filament v4 resource for UsersRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = 'customer_groups.relation_users';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('email')->email()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('customers.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('email')->label(__('customers.email'))->searchable()->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('customers.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([])->headerActions([Tables\Actions\AttachAction::make()->label(__('customer_groups.attach_user'))])->actions([Tables\Actions\DetachAction::make()->label(__('customer_groups.detach_user'))])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()->label(__('customer_groups.detach_selected'))])]);
    }
}