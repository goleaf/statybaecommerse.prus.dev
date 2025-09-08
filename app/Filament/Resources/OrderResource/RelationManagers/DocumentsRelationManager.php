<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Document;
use App\Services\DocumentService;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('documents.document_information'))
                    ->components([
                        Forms\Components\TextInput::make('title')
                            ->label(__('documents.title'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('document_template_id')
                            ->label(__('documents.template'))
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->label(__('documents.status'))
                            ->options([
                                'draft' => __('documents.statuses.draft'),
                                'published' => __('documents.statuses.published'),
                                'archived' => __('documents.statuses.archived'),
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('documents.notes'))
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('documents.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template.name')
                    ->label(__('documents.template'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template.type')
                    ->label(__('documents.type'))
                    ->badge()
                    ->colors([
                        'success' => 'invoice',
                        'warning' => 'receipt',
                        'danger' => 'contract',
                        'info' => 'document',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('documents.status'))
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('format')
                    ->label(__('documents.format'))
                    ->badge()
                    ->colors([
                        'info' => 'html',
                        'success' => 'pdf',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('documents.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('documents.status'))
                    ->options([
                        'draft' => __('documents.statuses.draft'),
                        'published' => __('documents.statuses.published'),
                        'archived' => __('documents.statuses.archived'),
                    ]),

                Tables\Filters\SelectFilter::make('format')
                    ->label(__('documents.format'))
                    ->options([
                        'html' => 'HTML',
                        'pdf' => 'PDF',
                    ]),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['documentable_type'] = $this->getOwnerRecord()::class;
                        $data['documentable_id'] = $this->getOwnerRecord()->getKey();
                        $data['created_by'] = auth()->id();
                        $data['generated_at'] = now();
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('generate_pdf')
                    ->label(__('documents.generate_pdf'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (Document $record): bool => $record->format === 'html')
                    ->action(function (Document $record) {
                        $service = app(DocumentService::class);
                        $url = $service->generatePdf($record);
                        
                        return redirect($url);
                    }),

                Action::make('download')
                    ->label(__('documents.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (Document $record): bool => $record->isPdf() && $record->file_path)
                    ->url(fn (Document $record): string => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
