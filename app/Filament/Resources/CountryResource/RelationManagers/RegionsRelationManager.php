<?php

declare (strict_types=1);
namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\Region;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * RegionsRelationManager
 * 
 * Filament v4 resource for RegionsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RegionsRelationManager extends RelationManager
{
    protected static string $relationship = 'regions';
    protected static ?string $title = 'Regions';
    protected static ?string $modelLabel = 'Region';
    protected static ?string $pluralModelLabel = 'Regions';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('name_official')->maxLength(255), Forms\Components\TextInput::make('code')->maxLength(10), Forms\Components\TextInput::make('latitude')->numeric()->step(1.0E-6), Forms\Components\TextInput::make('longitude')->numeric()->step(1.0E-6), Forms\Components\Toggle::make('is_active')->default(true), Forms\Components\TextInput::make('sort_order')->numeric()->default(0)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')->columns([Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('name_official')->searchable()->sortable()->toggleable(), Tables\Columns\TextColumn::make('code')->searchable()->sortable()->badge(), Tables\Columns\IconColumn::make('is_active')->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')->trueColor('success')->falseColor('danger'), Tables\Columns\TextColumn::make('sort_order')->sortable()->toggleable(), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('is_active')->placeholder('All Regions')->trueLabel('Active Only')->falseLabel('Inactive Only')])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->defaultSort('sort_order');
    }
}