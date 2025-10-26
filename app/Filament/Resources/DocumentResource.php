<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\Order;
use App\Models\Scopes\StatusScope;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class DocumentResource extends Resource
{
    private const STATUS_OPTIONS = [
        'draft'     => 'Draft',
        'generated' => 'Generated',
        'sent'      => 'Sent',
        'published' => 'Published',
        'archived'  => 'Archived',
    ];

    private const FORMAT_OPTIONS = [
        'pdf'      => 'PDF',
        'html'     => 'HTML',
        'docx'     => 'DOCX',
        'markdown' => 'Markdown',
    ];

    private const DOCUMENTABLE_TYPE_OPTIONS = [
        Order::class => 'Order',
    ];

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document';

    protected static ?string $model = Document::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'System';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.documents.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.documents.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.documents.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            StatusScope::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        $statusRule = sprintf('in:%s', implode(',', array_keys(self::getStatusOptions())));
        $formatRule = sprintf('in:%s', implode(',', array_keys(self::getFormatOptions())));

        return $schema
            ->schema([
                SchemaSection::make(self::translateWithFallback('admin.documents.form.sections.basic_information', 'Document Details'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.title', 'Title'))
                                    ->required()
                                    ->maxLength(255),
                                Select::make('status')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.status', 'Status'))
                                    ->options(self::getStatusOptions())
                                    ->default('draft')
                                    ->required()
                                    ->rules([$statusRule]),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('format')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.format', 'Format'))
                                    ->options(self::getFormatOptions())
                                    ->default('pdf')
                                    ->required()
                                    ->rules([$formatRule]),
                                Select::make('document_template_id')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.document_template', 'Template'))
                                    ->relationship('template', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('documentable_type')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.documentable_type', 'Related Type'))
                                    ->options(self::getDocumentableTypeOptions())
                                    ->required(),
                                TextInput::make('documentable_id')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.documentable_id', 'Related ID'))
                                    ->numeric()
                                    ->required(),
                            ]),
                        SchemaGrid::make(1)
                            ->schema([
                                Textarea::make('content')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.content', 'Content'))
                                    ->rows(6)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ]),
                SchemaSection::make(self::translateWithFallback('admin.documents.form.sections.additional_details', 'Additional Details'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.created_by', 'Created By'))
                                    ->relationship('creator', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(fn (): ?int => Auth::id())
                                    ->nullable(),
                                Select::make('updated_by')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.updated_by', 'Updated By'))
                                    ->relationship('updater', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                FileUpload::make('file_path')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.file_path', 'File'))
                                    ->directory('documents')
                                    ->maxSize(10 * 1024)
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'text/html',
                                        'text/markdown',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        '.pdf',
                                        '.doc',
                                        '.docx',
                                        '.xls',
                                        '.xlsx',
                                        '.html',
                                        '.md',
                                        '.jpg',
                                        '.jpeg',
                                        '.png',
                                        '.webp',
                                    ])
                                    ->nullable(),
                                Textarea::make('description')
                                    ->label(self::translateWithFallback('admin.documents.form.fields.description', 'Description'))
                                    ->rows(4)
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(self::translateWithFallback('admin.documents.form.fields.title', 'Title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(self::translateWithFallback('admin.documents.form.fields.status', 'Status'))
                    ->badge()
                    ->colors([
                        'draft'     => 'gray',
                        'generated' => 'info',
                        'sent'      => 'warning',
                        'published' => 'success',
                        'archived'  => 'danger',
                    ])
                    ->sortable(),
                TextColumn::make('format')
                    ->label(self::translateWithFallback('admin.documents.form.fields.format', 'Format'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label(self::translateWithFallback('admin.documents.form.fields.document_template', 'Template'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('documentable_type')
                    ->label(self::translateWithFallback('admin.documents.form.fields.documentable_type', 'Related Type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('documentable_id')
                    ->label(self::translateWithFallback('admin.documents.form.fields.documentable_id', 'Related ID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('file_path')
                    ->label(self::translateWithFallback('admin.documents.form.fields.file_path', 'Has File'))
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->color(static fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('creator.name')
                    ->label(self::translateWithFallback('admin.documents.form.fields.created_by', 'Created By'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('generated_at')
                    ->label(self::translateWithFallback('admin.documents.form.fields.generated_at', 'Generated At'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(self::translateWithFallback('admin.documents.form.fields.created_at', 'Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(self::translateWithFallback('admin.documents.form.fields.updated_at', 'Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(self::translateWithFallback('admin.documents.form.fields.status', 'Status'))
                    ->options(self::getStatusOptions()),
                SelectFilter::make('format')
                    ->label(self::translateWithFallback('admin.documents.form.fields.format', 'Format'))
                    ->options(self::getFormatOptions()),
                SelectFilter::make('template')
                    ->label(self::translateWithFallback('admin.documents.form.fields.document_template', 'Template'))
                    ->relationship('template', 'name')
                    ->searchable(),
                SelectFilter::make('creator')
                    ->label(self::translateWithFallback('admin.documents.form.fields.created_by', 'Created By'))
                    ->relationship('creator', 'name')
                    ->searchable(),
                TernaryFilter::make('is_generated')
                    ->label(self::translateWithFallback('admin.documents.filters.is_generated', 'Generated'))
                    ->queries(
                        true: static fn (Builder $query): Builder => $query->whereNotNull('generated_at'),
                        false: static fn (Builder $query): Builder => $query->whereNull('generated_at'),
                    ),
                TernaryFilter::make('has_file')
                    ->label(self::translateWithFallback('admin.documents.filters.has_file', 'Has File'))
                    ->queries(
                        true: static fn (Builder $query): Builder => $query->whereNotNull('file_path'),
                        false: static fn (Builder $query): Builder => $query->whereNull('file_path'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('generate')
                    ->label(self::translateWithFallback('admin.documents.actions.generate', 'Generate'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Document $record): bool => $record->status === 'draft')
                    ->action(function (Document $record): void {
                        $record->update([
                            'status'       => 'generated',
                            'generated_at' => now(),
                        ]);

                        Notification::make()
                            ->title(self::translateWithFallback('admin.documents.notifications.generated', 'Document generated successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('publish')
                    ->label(self::translateWithFallback('admin.documents.actions.publish', 'Publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Document $record): bool => in_array($record->status, ['generated', 'sent'], true))
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'published']);

                        Notification::make()
                            ->title(self::translateWithFallback('admin.documents.notifications.published', 'Document published successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('archive')
                    ->label(self::translateWithFallback('admin.documents.actions.archive', 'Archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Document $record): bool => $record->status === 'published')
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'archived']);

                        Notification::make()
                            ->title(self::translateWithFallback('admin.documents.notifications.archived', 'Document archived successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('generate')
                        ->label(self::translateWithFallback('admin.documents.bulk_actions.generate', 'Generate'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(static function (Document $record): void {
                                $record->update([
                                    'status'       => 'generated',
                                    'generated_at' => now(),
                                ]);
                            });

                            Notification::make()
                                ->title(self::translateWithFallback('admin.documents.notifications.generated_bulk', 'Documents generated successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('publish')
                        ->label(self::translateWithFallback('admin.documents.bulk_actions.publish', 'Publish'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(static function (Document $record): void {
                                $record->update(['status' => 'published']);
                            });

                            Notification::make()
                                ->title(self::translateWithFallback('admin.documents.notifications.published_bulk', 'Documents published successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('archive')
                        ->label(self::translateWithFallback('admin.documents.bulk_actions.archive', 'Archive'))
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(static function (Document $record): void {
                                $record->update(['status' => 'archived']);
                            });

                            Notification::make()
                                ->title(self::translateWithFallback('admin.documents.notifications.archived_bulk', 'Documents archived successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index'  => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view'   => Pages\ViewDocument::route('/{record}'),
            'edit'   => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getStatusOptions(): array
    {
        return collect(self::STATUS_OPTIONS)
            ->mapWithKeys(fn (string $label, string $value): array => [
                $value => self::translateWithFallback("admin.documents.statuses.{$value}", $label),
            ])
            ->all();
    }

    public static function getFormatOptions(): array
    {
        return collect(self::FORMAT_OPTIONS)
            ->mapWithKeys(fn (string $label, string $value): array => [
                $value => self::translateWithFallback("admin.documents.formats.{$value}", $label),
            ])
            ->all();
    }

    public static function getDocumentableTypeOptions(): array
    {
        return collect(self::DOCUMENTABLE_TYPE_OPTIONS)
            ->mapWithKeys(fn (string $label, string $value): array => [
                $value => self::translateWithFallback("admin.documents.documentable_types.{$value}", $label),
            ])
            ->all();
    }

    private static function translateWithFallback(string $key, string $fallback): string
    {
        $translation = __($key);

        return $translation === $key ? $fallback : $translation;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) Document::count();

        return $count > 0 ? (string) $count : null;
    }
}
