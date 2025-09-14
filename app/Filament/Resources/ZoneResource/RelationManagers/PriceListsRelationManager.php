<?php

declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final /**
 * PriceListsRelationManager
 * 
 * Filament resource for admin panel management.
 */
class PriceListsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceLists';

    protected static ?string $title = 'zones.price_lists';

    public function form(Schema $form): Schema
    {
        return $schema->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('price_lists.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label(__('price_lists.code'))
                    ->required()
                    ->maxLength(50),
                Forms\Components\Textarea::make('description')
                    ->label(__('price_lists.description'))
                    ->rows(3),
                Forms\Components\Toggle::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('price_lists.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('price_lists.description'))
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('zones.create_price_list')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
