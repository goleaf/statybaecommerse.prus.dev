<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Document;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('template_id')
                    ->relationship('template', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->poll('5s')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label(__('admin.fields.template'))
                    ->sortable(),
                TextColumn::make('format')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf'   => 'danger',
                        'html'  => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'generated' => 'info',
                        'draft'     => 'warning',
                        'archived'  => 'gray',
                        default     => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // We typically generate documents via the main resource action,
                // but a manual create can remain if needed.
                // CreateAction::make(),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('admin.actions.view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn (Document $record): ?string => $record->getFileUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Document $record): bool => $record->isGenerated()),
                Action::make('download')
                    ->label(__('admin.actions.download'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (Document $record): ?string => $record->getFileUrl())
                    ->openUrlInNewTab() // Often better for downloads to prevent navigating away
                    ->visible(fn (Document $record): bool => $record->isGenerated() && $record->isDownloadable()),
            ])
            ->bulkActions([
                //
            ]);
    }
}
