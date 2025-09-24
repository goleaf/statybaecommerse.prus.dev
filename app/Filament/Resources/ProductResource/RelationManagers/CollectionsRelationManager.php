<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    protected static ?string $title = 'Collections';

    protected static ?string $modelLabel = 'Collection';

    protected static ?string $pluralModelLabel = 'Collections';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('collections.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('collections.fields.slug'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('description')
                    ->label(__('collections.fields.description'))
                    ->limit(50)
                    ->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->defaultSort('name');
    }
}
