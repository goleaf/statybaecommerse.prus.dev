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

class DocumentsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    protected static ?string $modelLabel = 'Document';

    protected static ?string $pluralModelLabel = 'Documents';

    public function table(Table $table): Table|array
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('documents.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('documents.fields.type'))
                    ->badge(),
                TextColumn::make('file_name')
                    ->label(__('documents.fields.file_name'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('file_size')
                    ->label(__('documents.fields.file_size'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : ''),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
