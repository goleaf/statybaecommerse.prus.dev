<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('similar_product_id')
                    ->relationship('similarProduct', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('similarity_score')
                    ->numeric()
                    ->required(),
                TextInput::make('algorithm_type')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('similarProduct.name')
                    ->label('Similar Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('algorithm_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
