<?php

declare (strict_types=1);
namespace App\Filament\Resources\CityResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
/**
 * AddressesRelationManager
 * 
 * Filament v4 resource for AddressesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';
    protected static ?string $title = 'Addresses';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\Textarea::make('description')->maxLength(1000)->rows(3), Forms\Components\TextInput::make('address_line_1')->maxLength(500), Forms\Components\TextInput::make('address_line_2')->maxLength(500), Forms\Components\TextInput::make('postal_code')->maxLength(20), Forms\Components\TextInput::make('phone')->tel()->maxLength(20), Forms\Components\TextInput::make('email')->email()->maxLength(255), Forms\Components\Toggle::make('is_enabled')->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->searchable()->sortable(), Tables\Columns\TextColumn::make('address_line_1')->searchable()->toggleable(), Tables\Columns\TextColumn::make('postal_code')->searchable()->sortable(), Tables\Columns\TextColumn::make('phone')->searchable()->toggleable(), Tables\Columns\TextColumn::make('email')->searchable()->toggleable(), Tables\Columns\IconColumn::make('is_enabled')->boolean(), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TrashedFilter::make()])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]));
    }
}