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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * DocumentResource
 *
 * Filament v4 resource for Document management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    /** @var UnitEnum|string|null */    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Documents;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.documents.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Documents';
    }

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
                                ])
                                ->default('draft')
                                ->required()
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
                        ->columnSpanFull(),
                    Textarea::make('variables')
                        ->label(__('admin.documents.form.fields.variables'))
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make(__('admin.documents.form.sections.organization'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('documentable_type')
                                ->label(__('admin.documents.form.fields.documentable_type'))
                                ->options([
                                    Order::class => 'Order',
                                ])
                                ->columnSpan(1),
                            Select::make('documentable_id')
                                ->label(__('admin.documents.form.fields.documentable_id'))
                                ->relationship('documentable', 'id')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                        ]),
                    Select::make('format')
                        ->label(__('admin.documents.form.fields.format'))
                        ->options([
                            'pdf' => 'PDF',
                            'html' => 'HTML',
                            'docx' => 'DOCX',
                        ])
                        ->default('pdf')
                        ->required(),
                    Select::make('created_by')
                        ->label(__('admin.documents.form.fields.created_by'))
                        ->relationship('creator', 'name')
                        ->searchable()
                        ->preload()
                        ->default(auth()->id()),
                ])
                ->columns(1),
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
                TextColumn::make('title')
                    ->label(__('admin.documents.form.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label(__('admin.documents.form.fields.template'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('admin.documents.form.fields.status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'generated',
                        'primary' => 'published',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        default => $state,
                    }),
                TextColumn::make('format')
                    ->label(__('admin.documents.form.fields.format'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label(__('admin.documents.form.fields.created_by'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.documents.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('generated_at')
                    ->label(__('admin.documents.form.fields.generated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.documents.form.fields.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                    ]),
                SelectFilter::make('format')
                    ->label(__('admin.documents.form.fields.format'))
                    ->options([
                        'pdf' => 'PDF',
                        'html' => 'HTML',
                        'docx' => 'DOCX',
                    ]),
                SelectFilter::make('template')
                    ->label(__('admin.documents.form.fields.template'))
                    ->relationship('template', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('generate')
                    ->label(__('admin.documents.actions.generate'))
                    ->icon('heroicon-o-cog-6-tooth')
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
                    ->visible(fn (Document $record): bool => $record->status === 'draft'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('generate')
                        ->label(__('admin.documents.actions.generate'))
                        ->icon('heroicon-o-cog-6-tooth')
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
