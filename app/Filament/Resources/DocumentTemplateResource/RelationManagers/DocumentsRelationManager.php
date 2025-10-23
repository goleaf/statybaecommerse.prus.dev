<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\RelationManagers;


use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

final class DocumentsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'admin/documents.plural';

    public function form(Schema $schema): Schema   
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table   
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin/documents.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin/documents.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'generated' => 'success',
                        'published' => 'info',
                        'archived'  => 'gray',
                        default     => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): ?string => $state ? __('admin/documents.status.' . $state) : null),
                TextColumn::make('format')
                    ->label(__('admin/documents.format'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => $state ? Str::upper($state) : null)
                    ->toggleable(),
                IconColumn::make('is_public')
                    ->label(__('admin/documents.form.fields.is_public'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin/document_templates.form.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/document_templates.form.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin/documents.status'))
                    ->options([
                        'draft'     => __('admin/documents.status.draft'),
                        'generated' => __('admin/documents.status.generated'),
                        'published' => __('admin/documents.status.published'),
                        'archived'  => __('admin/documents.status.archived'),
                    ]),
                TernaryFilter::make('is_public')
                    ->label(__('admin/documents.form.fields.is_public')),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}

