<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;


use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;

final class DocumentsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'admin.sections.documents';

    public function form(Schema $schema): Schema   
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'invoice'     => 'Invoice',
                        'receipt'     => 'Receipt',
                        'contract'    => 'Contract',
                        'agreement'   => 'Agreement',
                        'certificate' => 'Certificate',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'generated' => 'Generated',
                        'sent'      => 'Sent',
                        'archived'  => 'Archived',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240),
            ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'invoice'     => 'primary',
                        'receipt'     => 'success',
                        'contract'    => 'warning',
                        'agreement'   => 'info',
                        'certificate' => 'secondary',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'generated' => 'info',
                        'sent'      => 'success',
                        'archived'  => 'warning',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_path')
                    ->label(__('admin.fields.file'))
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : 'No file')
                    ->url(fn ($record) => $record->file_path ? SecureStorage::temporarySignedUrl(
                        $record->file_path,
                        now()->addMinutes((int) config('media-security.url_lifetime', 30)),
                        true
                    ) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'invoice'     => 'Invoice',
                        'receipt'     => 'Receipt',
                        'contract'    => 'Contract',
                        'agreement'   => 'Agreement',
                        'certificate' => 'Certificate',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'generated' => 'Generated',
                        'sent'      => 'Sent',
                        'archived'  => 'Archived',
                    ]),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}