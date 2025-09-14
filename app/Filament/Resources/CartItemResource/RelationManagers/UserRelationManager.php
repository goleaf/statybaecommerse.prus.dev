<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * UserRelationManager
 * 
 * Filament v4 resource for UserRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';
    protected static ?string $title = 'admin.cart_items.relations.user';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->label(__('admin.users.fields.name'))->required()->maxLength(255), Forms\Components\TextInput::make('email')->label(__('admin.users.fields.email'))->email()->required()->maxLength(255), Forms\Components\TextInput::make('phone')->label(__('admin.users.fields.phone'))->tel()->maxLength(255)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('admin.users.fields.name'))->searchable()->sortable()->weight('medium'), Tables\Columns\TextColumn::make('email')->label(__('admin.users.fields.email'))->searchable()->sortable()->copyable()->copyMessage(__('admin.common.copied')), Tables\Columns\TextColumn::make('phone')->label(__('admin.users.fields.phone'))->searchable()->toggleable(), Tables\Columns\IconColumn::make('email_verified_at')->label(__('admin.users.fields.email_verified'))->boolean()->getStateUsing(fn($record) => !is_null($record->email_verified_at)), Tables\Columns\IconColumn::make('is_active')->label(__('admin.users.fields.is_active'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('admin.users.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('email_verified_at')->label(__('admin.users.filters.email_verified')), Tables\Filters\TernaryFilter::make('is_active')->label(__('admin.users.filters.active_only'))])->headerActions([Tables\Actions\CreateAction::make(), Tables\Actions\AttachAction::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}