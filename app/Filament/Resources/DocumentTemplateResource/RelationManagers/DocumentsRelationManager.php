<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\RelationManagers;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                TextInput::make('format')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('format')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Document::FORMAT_PDF  => 'danger',
                        Document::FORMAT_HTML => 'info',
                        Document::FORMAT_DOCX => 'warning',
                        default               => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Document::STATUS_PUBLISHED => 'success',
                        Document::STATUS_GENERATED => 'info',
                        Document::STATUS_DRAFT     => 'warning',
                        Document::STATUS_ARCHIVED  => 'gray',
                        default                    => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.status'))
                    ->options(self::statusOptions())
                    ->searchable(),
            ])
            ->actions([
                Action::make('download')
                    ->label(__('admin.actions.download'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (Document $record): ?string => $record->getFileUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Document $record): bool => $record->isGenerated()),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            Document::STATUS_DRAFT     => Str::headline(Document::STATUS_DRAFT),
            Document::STATUS_GENERATED => Str::headline(Document::STATUS_GENERATED),
            Document::STATUS_PUBLISHED => Str::headline(Document::STATUS_PUBLISHED),
            Document::STATUS_ARCHIVED  => Str::headline(Document::STATUS_ARCHIVED),
        ];
    }
}
