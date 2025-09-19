<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.documents.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.documents.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Document Tabs')
                ->tabs([
                    Tab::make(__('admin.documents.form.tabs.basic_information'))
                        ->components([
                            Section::make(__('admin.documents.form.sections.basic_information'))
                                ->components([
                                    Grid::make(2)
                                        ->components([
                                            TextInput::make('title')
                                                ->label(__('admin.documents.form.fields.title'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            Select::make('status')
                                                ->label(__('admin.documents.form.fields.status'))
                                                ->options([
                                                    'draft' => __('admin.documents.status.draft'),
                                                    'generated' => __('admin.documents.status.generated'),
                                                    'published' => __('admin.documents.status.published'),
                                                    'archived' => __('admin.documents.status.archived'),
                                                ])
                                                ->default('draft')
                                                ->columnSpan(1),
                                        ]),
                                    Select::make('document_template_id')
                                        ->label(__('admin.documents.form.fields.template'))
                                        ->relationship('template', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    RichEditor::make('content')
                                        ->label(__('admin.documents.form.fields.content'))
                                        ->columnSpanFull()
                                        ->toolbarButtons([
                                            'attachFiles',
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
                                            'underline',
                                            'undo',
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.documents.form.tabs.variables'))
                        ->components([
                            Section::make(__('admin.documents.form.sections.variables'))
                                ->components([
                                    KeyValue::make('variables')
                                        ->label(__('admin.documents.form.fields.variables'))
                                        ->keyLabel(__('admin.documents.form.fields.variable_name'))
                                        ->valueLabel(__('admin.documents.form.fields.variable_value'))
                                        ->addActionLabel(__('admin.documents.form.actions.add_variable'))
                                        ->columnSpanFull(),
                                    Placeholder::make('variables_help')
                                        ->content(__('admin.documents.form.help.variables'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.documents.form.tabs.organization'))
                        ->components([
                            Section::make(__('admin.documents.form.sections.organization'))
                                ->components([
                                    Grid::make(2)
                                        ->components([
                                            Select::make('documentable_type')
                                                ->label(__('admin.documents.form.fields.documentable_type'))
                                                ->options([
                                                    Order::class => 'Order',
                                                ])
                                                ->required()
                                                ->columnSpan(1),
                                            Select::make('documentable_id')
                                                ->label(__('admin.documents.form.fields.documentable_id'))
                                                ->relationship('documentable', 'id')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->columnSpan(1),
                                        ]),
                                    Grid::make(2)
                                        ->components([
                                            Select::make('format')
                                                ->label(__('admin.documents.form.fields.format'))
                                                ->options([
                                                    'pdf' => 'PDF',
                                                    'html' => 'HTML',
                                                    'docx' => 'DOCX',
                                                ])
                                                ->default('pdf')
                                                ->required()
                                                ->columnSpan(1),
                                            Select::make('created_by')
                                                ->label(__('admin.documents.form.fields.created_by'))
                                                ->relationship('creator', 'name')
                                                ->default(auth()->id())
                                                ->required()
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.documents.form.tabs.file_management'))
                        ->components([
                            Section::make(__('admin.documents.form.sections.file_management'))
                                ->components([
                                    FileUpload::make('file_path')
                                        ->label(__('admin.documents.form.fields.file_path'))
                                        ->disk('public')
                                        ->directory('documents')
                                        ->acceptedFileTypes(['application/pdf', 'text/html', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(10240)  // 10MB
                                        ->columnSpanFull(),
                                    DateTimePicker::make('generated_at')
                                        ->label(__('admin.documents.form.fields.generated_at'))
                                        ->disabled()
                                        ->columnSpan(1),
                                    Toggle::make('is_public')
                                        ->label(__('admin.documents.form.fields.is_public'))
                                        ->default(false)
                                        ->columnSpan(1),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.documents.form.fields.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->label(__('admin.documents.form.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('template.name')
                    ->label(__('admin.documents.form.fields.template'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                BadgeColumn::make('status')
                    ->label(__('admin.documents.form.fields.status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'generated',
                        'primary' => 'published',
                        'danger' => 'archived',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                        default => $state,
                    }),
                TextColumn::make('format')
                    ->label(__('admin.documents.form.fields.format'))
                    ->badge()
                    ->colors([
                        'danger' => 'pdf',
                        'info' => 'html',
                        'success' => 'docx',
                    ])
                    ->sortable(),
                TextColumn::make('documentable_type')
                    ->label(__('admin.documents.form.fields.documentable_type'))
                    ->formatStateUsing(fn(string $state): string => class_basename($state))
                    ->badge()
                    ->color('secondary')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('documentable_id')
                    ->label(__('admin.documents.form.fields.documentable_id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label(__('admin.documents.form.fields.created_by'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label(__('admin.documents.form.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('generated_at')
                    ->label(__('admin.documents.form.fields.generated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('file_path')
                    ->label(__('admin.documents.form.fields.file_attached'))
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-document-text')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ])
                    ->multiple(),
                SelectFilter::make('format')
                    ->options([
                        'pdf' => 'PDF',
                        'html' => 'HTML',
                        'docx' => 'DOCX',
                    ])
                    ->multiple(),
                SelectFilter::make('template')
                    ->relationship('template', 'name')
                    ->multiple(),
                SelectFilter::make('creator')
                    ->relationship('creator', 'name')
                    ->multiple(),
                TernaryFilter::make('is_generated')
                    ->label(__('admin.documents.filters.is_generated'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('generated_at'),
                        false: fn(Builder $query) => $query->whereNull('generated_at'),
                    ),
                TernaryFilter::make('has_file')
                    ->label(__('admin.documents.filters.has_file'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('file_path'),
                        false: fn(Builder $query) => $query->whereNull('file_path'),
                    ),
                DateFilter::make('created_at')
                    ->label(__('admin.documents.filters.created_at')),
                DateFilter::make('generated_at')
                    ->label(__('admin.documents.filters.generated_at')),
                Filter::make('recent')
                    ->label(__('admin.documents.filters.recent'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
                Filter::make('old_documents')
                    ->label(__('admin.documents.filters.old_documents'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '<', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('generate')
                    ->label(__('admin.documents.actions.generate'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->action(function (Document $record): void {
                        $record->update([
                            'status' => 'generated',
                            'generated_at' => now(),
                        ]);
                        FilamentNotification::make()
                            ->title(__('admin.documents.generated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Document $record): bool => $record->status === 'draft'),
                \Filament\Actions\Action::make('publish')
                    ->label(__('admin.documents.actions.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'published']);
                        FilamentNotification::make()
                            ->title(__('admin.documents.published_successfully'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Document $record): bool => $record->status === 'generated'),
                \Filament\Actions\Action::make('archive')
                    ->label(__('admin.documents.actions.archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'archived']);
                        FilamentNotification::make()
                            ->title(__('admin.documents.archived_successfully'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Document $record): bool => in_array($record->status, ['generated', 'published'])),
                \Filament\Actions\Action::make('download')
                    ->label(__('admin.documents.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn(Document $record): string => $record->getFileUrl() ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn(Document $record): bool => !empty($record->file_path)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('generate')
                        ->label(__('admin.documents.actions.generate'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function (Document $record): void {
                                if ($record->status === 'draft') {
                                    $record->update([
                                        'status' => 'generated',
                                        'generated_at' => now(),
                                    ]);
                                }
                            });
                            FilamentNotification::make()
                                ->title(__('admin.documents.generated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('publish')
                        ->label(__('admin.documents.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (Document $record): void {
                                if ($record->status === 'generated') {
                                    $record->update(['status' => 'published']);
                                }
                            });
                            FilamentNotification::make()
                                ->title(__('admin.documents.published_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('archive')
                        ->label(__('admin.documents.actions.archive'))
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (Document $record): void {
                                if (in_array($record->status, ['generated', 'published'])) {
                                    $record->update(['status' => 'archived']);
                                }
                            });
                            FilamentNotification::make()
                                ->title(__('admin.documents.archived_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->groups([
                Group::make('status')
                    ->label(__('admin.documents.groups.status'))
                    ->collapsible(),
                Group::make('format')
                    ->label(__('admin.documents.groups.format'))
                    ->collapsible(),
                Group::make('template.name')
                    ->label(__('admin.documents.groups.template'))
                    ->collapsible(),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('sort_order')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
}
