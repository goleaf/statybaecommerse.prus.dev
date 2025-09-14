<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\Customer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * CustomersRelationManager
 * 
 * Filament v4 resource for CustomersRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';
    protected static ?string $title = 'Customers';
    protected static ?string $modelLabel = 'Customer';
    protected static ?string $pluralModelLabel = 'Customers';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('first_name')->required()->maxLength(255), Forms\Components\TextInput::make('last_name')->required()->maxLength(255), Forms\Components\TextInput::make('email')->email()->required()->maxLength(255), Forms\Components\TextInput::make('phone')->tel()->maxLength(20), Forms\Components\TextInput::make('country_code')->required()->maxLength(2), Forms\Components\Toggle::make('is_active')->default(true)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('first_name')->columns([Tables\Columns\TextColumn::make('first_name')->searchable()->sortable(), Tables\Columns\TextColumn::make('last_name')->searchable()->sortable(), Tables\Columns\TextColumn::make('email')->searchable()->sortable()->copyable(), Tables\Columns\TextColumn::make('phone')->searchable()->sortable()->toggleable(), Tables\Columns\TextColumn::make('country_code')->searchable()->sortable()->badge(), Tables\Columns\IconColumn::make('is_active')->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')->trueColor('success')->falseColor('danger'), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_active')->placeholder('All Customers')->trueLabel('Active Only')->falseLabel('Inactive Only')])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->defaultSort('created_at', 'desc');
    }
}