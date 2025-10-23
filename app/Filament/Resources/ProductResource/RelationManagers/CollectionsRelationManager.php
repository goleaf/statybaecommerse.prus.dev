<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

class CollectionsRelationManager extends BaseRelationManager
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
                RelationManagerRepeaterAction::make(),
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->defaultSort('name');
    }
}
