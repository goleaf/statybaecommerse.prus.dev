<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * AddressesRelationManager
 * 
 * Filament v4 resource for AddressesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';
    protected static ?string $title = 'Addresses';
    protected static ?string $modelLabel = 'Address';
    protected static ?string $pluralModelLabel = 'Addresses';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('street')->required()->maxLength(255), Forms\Components\TextInput::make('city')->required()->maxLength(255), Forms\Components\TextInput::make('postal_code')->maxLength(20), Forms\Components\TextInput::make('state')->maxLength(255), Forms\Components\TextInput::make('country_code')->required()->maxLength(2), Forms\Components\TextInput::make('latitude')->numeric()->step(1.0E-6), Forms\Components\TextInput::make('longitude')->numeric()->step(1.0E-6)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('street')->columns([Tables\Columns\TextColumn::make('street')->searchable()->sortable(), Tables\Columns\TextColumn::make('city')->searchable()->sortable(), Tables\Columns\TextColumn::make('postal_code')->searchable()->sortable(), Tables\Columns\TextColumn::make('state')->searchable()->sortable(), Tables\Columns\TextColumn::make('country_code')->searchable()->sortable()->badge(), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}