<?php declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CountriesRelationManager extends RelationManager
{
    protected static string $relationship = 'countries';

    protected static ?string $title = 'zones.countries';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('countries.name'))
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label(__('countries.code'))
                    ->required(),
                    ->maxLength(2),
                Forms\Components\TextInput::make('iso3')
                    ->label(__('countries.iso3'))
                    ->maxLength(3),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('countries.name'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('countries.code'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('iso3')
                    ->label(__('countries.iso3'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('countries.is_active'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('countries.is_active')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('zones.attach_country'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('zones.detach_country')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('zones.detach_selected_countries')),
                ]),
            ]);
    }
}
