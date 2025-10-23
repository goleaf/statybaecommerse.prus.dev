<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

final class ValuesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $title = 'Attribute Values';

    public function form(Schema $form): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->label(__('translations.value'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table|array
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label(__('translations.value'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
