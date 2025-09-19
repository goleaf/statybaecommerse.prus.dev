<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'admin.sections.documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required(),
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'invoice' => 'Invoice',
                        'receipt' => 'Receipt',
                        'contract' => 'Contract',
                        'agreement' => 'Agreement',
                        'certificate' => 'Certificate',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'generated' => 'Generated',
                        'sent' => 'Sent',
                        'archived' => 'Archived',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240), // 10MB
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.fields.type'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'invoice' => 'primary',
                        'receipt' => 'success',
                        'contract' => 'warning',
                        'agreement' => 'info',
                        'certificate' => 'secondary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'sent' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_path')
                    ->label(__('admin.fields.file'))
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : 'No file')
                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'invoice' => 'Invoice',
                        'receipt' => 'Receipt',
                        'contract' => 'Contract',
                        'agreement' => 'Agreement',
                        'certificate' => 'Certificate',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'generated' => 'Generated',
                        'sent' => 'Sent',
                        'archived' => 'Archived',
                    ]),
            ])
            ->headerActions([
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
