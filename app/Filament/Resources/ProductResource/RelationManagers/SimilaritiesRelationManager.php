<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.similarities');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('similar_product_id')
                    ->label(__('messages.similar_product'))
                    ->relationship('similarProduct', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('similarity_score')
                    ->label(__('messages.similarity_score'))
                    ->numeric()
                    ->required(),
                TextInput::make('algorithm_type')
                    ->label(__('messages.algorithm_type'))
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('similarProduct.name')
                    ->label(__('messages.similar_product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('messages.similarity_score'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('algorithm_type')
                    ->label(__('messages.algorithm_type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->label(__('messages.calculated_at'))
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
