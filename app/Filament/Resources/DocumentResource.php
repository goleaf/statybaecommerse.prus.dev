<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

final class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document';

    protected static UnitEnum|string|null $navigationGroup = 'Documents';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('documents.document_information'))
                    ->components([
                        Forms\Components\Select::make('document_template_id')
                            ->label(__('documents.template'))
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $template = DocumentTemplate::find($state);
                                    if ($template) {
                                        $set('content', $template->content);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('title')
                            ->label(__('documents.title'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label(__('documents.status'))
                            ->required()
                            ->options([
                                'draft' => __('documents.statuses.draft'),
                                'published' => __('documents.statuses.published'),
                                'archived' => __('documents.statuses.archived'),
                            ])
                            ->default('draft'),

                        Forms\Components\Select::make('format')
                            ->label(__('documents.format'))
                            ->required()
                            ->options([
                                'html' => 'HTML',
                                'pdf' => 'PDF',
                            ])
                            ->default('html'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('documents.content'))
                    ->components([
                        Forms\Components\RichEditor::make('content')
                            ->label(__('documents.content'))
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table',
                                'undo',
                            ]),
                    ]),

                Forms\Components\Section::make(__('documents.variables'))
                    ->components([
                        Forms\Components\KeyValue::make('variables')
                            ->label(__('documents.variables'))
                            ->keyLabel(__('documents.variable_name'))
                            ->valueLabel(__('documents.variable_value'))
                            ->addActionLabel(__('documents.add_variable'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make(__('documents.metadata'))
                    ->components([
                        Forms\Components\TextInput::make('documentable_type')
                            ->label(__('documents.related_model_type'))
                            ->disabled(),

                        Forms\Components\TextInput::make('documentable_id')
                            ->label(__('documents.related_model_id'))
                            ->disabled(),

                        Forms\Components\TextInput::make('file_path')
                            ->label(__('documents.file_path'))
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('generated_at')
                            ->label(__('documents.generated_at'))
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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

                Tables\Columns\TextColumn::make('documentable_type')
                    ->label(__('documents.related_model'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('documents.created_by'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('documents.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('template')
                    ->label(__('documents.template'))
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload(),

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

                Tables\Filters\Filter::make('created_at')
                    ->label(__('documents.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('documents.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('documents.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('generate_pdf')
                    ->label(__('documents.generate_pdf'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (Document $record): bool => $record->format === 'html')
                    ->action(function (Document $record) {
                        $service = app(DocumentService::class);
                        $url = $service->generatePdf($record);
                        
                        return redirect($url);
                    }),

                Tables\Actions\Action::make('download')
                    ->label(__('documents.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (Document $record): bool => $record->isPdf() && $record->file_path)
                    ->url(fn (Document $record): string => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['template', 'creator']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'template.name'];
    }
}
