<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Models\Document;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Collection Documents';

    public function form(Schema $formSchema): Schema
    {
        return $schemaSchema
            ->components([
                Forms\Components\Select::make('document_template_id')
                    ->label(__('admin.documents.fields.template'))
                    ->relationship('documentTemplate', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label(__('admin.documents.fields.title'))
                    ->required(),
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label(__('admin.documents.fields.description'))
                    ->rows(3),
                    ->maxLength(1000),

                Forms\Components\Select::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ])
                    ->default('draft')
                    ->required(),

                Forms\Components\Select::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->options([
                        'html' => 'HTML',
                        'pdf' => 'PDF',
                        'docx' => 'DOCX',
                        'xlsx' => 'XLSX',
                    ])
                    ->default('pdf')
                    ->required(),

                Forms\Components\KeyValue::make('variables')
                    ->label(__('admin.documents.fields.variables'))
                    ->keyLabel(__('admin.documents.fields.variable_key'))
                    ->valueLabel(__('admin.documents.fields.variable_value'))
                    ->addActionLabel(__('admin.documents.actions.add_variable')),

                Forms\Components\FileUpload::make('file_path')
                    ->label(__('admin.documents.fields.file'))
                    ->directory('documents/collections')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
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
                    ->label(__('admin.documents.fields.title'))
                    ->searchable(),
                    ->sortable(),
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('documentTemplate.name')
                    ->label(__('admin.documents.fields.template'))
                    ->sortable(),
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->colors([
                        'secondary' => 'draft',
                        'primary' => 'generated',
                        'success' => 'published',
                        'warning' => 'archived',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->colors([
                        'info' => 'pdf',
                        'success' => 'html',
                        'warning' => 'docx',
                        'danger' => 'xlsx',
                    ])
                    ->formatStateUsing(fn(string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('file_size')
                    ->label(__('admin.documents.fields.file_size'))
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $bytes = (int) $state;
                        $units = ['B', 'KB', 'MB', 'GB'];
                        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                            $bytes /= 1024;
                        }
                        return round($bytes, 2) . ' ' . $units[$i];
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('generated_at')
                    ->label(__('admin.documents.fields.generated_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.documents.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.documents.filters.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ]),

                Tables\Filters\SelectFilter::make('format')
                    ->label(__('admin.documents.filters.format'))
                    ->options([
                        'html' => 'HTML',
                        'pdf' => 'PDF',
                        'docx' => 'DOCX',
                        'xlsx' => 'XLSX',
                    ]),

                Tables\Filters\SelectFilter::make('document_template_id')
                    ->label(__('admin.documents.filters.template'))
                    ->relationship('documentTemplate', 'name')
                    ->searchable(),
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.documents.actions.create_document')),

                Tables\Actions\Action::make('generate_document')
                    ->label(__('admin.documents.actions.generate_document'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('template_id')
                            ->label(__('admin.documents.fields.template'))
                            ->relationship('documentTemplate', 'name')
                            ->searchable(),
                            ->preload(),
                            ->required(),

                        Forms\Components\Select::make('format')
                            ->label(__('admin.documents.fields.format'))
                            ->options([
                                'pdf' => 'PDF',
                                'html' => 'HTML',
                                'docx' => 'DOCX',
                            ])
                            ->default('pdf')
                            ->required(),

                        Forms\Components\KeyValue::make('variables')
                            ->label(__('admin.documents.fields.variables'))
                            ->keyLabel(__('admin.documents.fields.variable_key'))
                            ->valueLabel(__('admin.documents.fields.variable_value')),
                    ])
                    ->action(function (array $data, $record) {
                        // This would typically call a document generation service
                        // For now, we'll just create a placeholder document
                        $record->documents()->create([
                            'document_template_id' => $data['template_id'],
                            'title' => 'Generated Document',
                            'status' => 'generated',
                            'format' => $data['format'],
                            'variables' => $data['variables'] ?? [],
                            'generated_at' => now(),
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.documents.actions.view_document')),

                EditAction::make()
                    ->label(__('admin.documents.actions.edit_document')),

                Tables\Actions\Action::make('download')
                    ->label(__('admin.documents.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn(Document $record): string => $record->file_path ? route('documents.download', $record) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn(Document $record): bool => !empty($record->file_path)),

                DeleteAction::make()
                    ->label(__('admin.documents.actions.delete_document'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.documents.confirmations.delete_document')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.documents.actions.delete_documents'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.documents.confirmations.delete_documents')),

                    Tables\Actions\BulkAction::make('change_status')
                        ->label(__('admin.documents.actions.change_status'))
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label(__('admin.documents.fields.status'))
                                ->options([
                                    'draft' => __('admin.documents.status.draft'),
                                    'generated' => __('admin.documents.status.generated'),
                                    'published' => __('admin.documents.status.published'),
                                    'archived' => __('admin.documents.status.archived'),
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['status' => $data['status']]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.documents.confirmations.change_status')),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
