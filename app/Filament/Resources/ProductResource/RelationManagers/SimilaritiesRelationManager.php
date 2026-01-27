<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScopes();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('similar_product_id')
                ->label(__('admin.products.similar_product'))
                ->relationship('similarProduct', 'name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('algorithm_type')
                ->label(__('admin.products.algorithm_type'))
                ->required()
                ->maxLength(100)
                ->default('manual'),
            TextInput::make('similarity_score')
                ->label(__('admin.products.similarity_score'))
                ->numeric()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('algorithm_type')
            ->columns([
                TextColumn::make('similarProduct.name')
                    ->label(__('admin.products.similar_product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('algorithm_type')
                    ->label(__('admin.products.algorithm_type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('admin.products.similarity_score'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->label(__('admin.products.calculated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
