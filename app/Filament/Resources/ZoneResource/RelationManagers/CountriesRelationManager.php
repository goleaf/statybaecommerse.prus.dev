<?php

declare (strict_types=1);
namespace App\Filament\Resources\ZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * CountriesRelationManager
 * 
 * Filament v4 resource for CountriesRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CountriesRelationManager extends RelationManager
{
    protected static string $relationship = 'countries';
    protected static ?string $title = 'zones.countries';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('name')->label(__('countries.name'))->required()->maxLength(255), Forms\Components\TextInput::make('code')->label(__('countries.code'))->required()->maxLength(2), Forms\Components\TextInput::make('iso3')->label(__('countries.iso3'))->maxLength(3)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->label(__('countries.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('code')->label(__('countries.code'))->searchable()->sortable(), Tables\Columns\TextColumn::make('iso3')->label(__('countries.iso3'))->searchable()->sortable(), Tables\Columns\IconColumn::make('is_active')->label(__('countries.is_active'))->boolean()])->filters([Tables\Filters\TernaryFilter::make('is_active')->label(__('countries.is_active'))])->headerActions([Tables\Actions\AttachAction::make()->label(__('zones.attach_country'))->preloadRecordSelect()])->actions([Tables\Actions\DetachAction::make()->label(__('zones.detach_country'))])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()->label(__('zones.detach_selected_countries'))])]);
    }
}