<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use Filament\Schemas\Schema;
use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Filament\Resources\DocumentTemplateResource\RelationManagers\DocumentsRelationManager;
use App\Models\DocumentTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

final class DocumentTemplateResource extends Resource
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): string
    {
        return 'Documents';
    }

    protected static ?string $model = DocumentTemplate::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('admin/document_templates.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin/document_templates.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('admin/document_templates.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema   
    {
        return $schema->schema([
            SchemaTabs::make('document_template_form')
                ->tabs([
                    SchemaTab::make(__('admin/document_templates.form.tabs.basic_information'))
                        ->schema([
                            SchemaSection::make(__('admin/document_templates.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('admin/document_templates.form.fields.name'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug')
                                                ->label(__('admin/document_templates.form.fields.slug'))
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true),
                                        ]),
                                    Textarea::make('description')
                                        ->label(__('admin/document_templates.form.fields.description'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('admin/document_templates.form.fields.type'))
                                                ->options(DocumentTemplateType::options())
                                                ->required()
                                                ->searchable(),
                                            Select::make('category')
                                                ->label(__('admin/document_templates.form.fields.category'))
                                                ->options(DocumentTemplateCategory::options())
                                                ->required()
                                                ->searchable(),
                                        ]),
                                    Toggle::make('is_active')
                                        ->label(__('admin/document_templates.form.fields.is_active'))
                                        ->default(true),
                                ]),
                        ]),
                    SchemaTab::make(__('admin/document_templates.form.tabs.content'))
                        ->schema([
                            SchemaSection::make(__('admin/document_templates.form.sections.content'))
                                ->schema([
                                    RichEditor::make('content')
                                        ->label(__('admin/document_templates.form.fields.content'))
                                        ->required()
                                        // Strip editor-generated tags so templates persist exactly as authored in tests and exports.
                                        ->mutateDehydratedStateUsing(fn (?string $state): ?string => $state !== null ? trim(strip_tags($state)) : null)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    SchemaTab::make(__('admin/document_templates.form.tabs.variables'))
                        ->schema([
                            SchemaSection::make(__('admin/document_templates.form.sections.variables'))
                                ->schema([
                                    Repeater::make('variables')
                                        ->label(__('admin/document_templates.form.fields.variables'))
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('admin/document_templates.form.fields.variable_name'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('description')
                                                ->label(__('admin/document_templates.form.fields.variable_description'))
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->default([])
                                        ->columns(2)
                                        ->mutateDehydratedStateUsing(fn (?array $state): array => self::normalizeVariablesState($state))
                                        ->afterStateHydrated(function (Repeater $component, ?array $state): void {
                                            $component->state(self::expandVariablesState($state));
                                        }),
                                ]),
                        ]),
                    SchemaTab::make(__('admin/document_templates.form.tabs.settings'))
                        ->schema([
                            SchemaSection::make(__('admin/document_templates.form.sections.settings'))
                                ->schema([
                                    KeyValue::make('settings')
                                        ->label(__('admin/document_templates.form.fields.settings'))
                                        ->keyLabel(__('admin/document_templates.form.fields.setting_key'))
                                        ->valueLabel(__('admin/document_templates.form.fields.setting_value'))
                                        ->addButtonLabel(__('filament-forms::components.key_value.buttons.add'))
                                        ->default([])
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    SchemaTab::make(__('admin/document_templates.form.tabs.preview'))
                        ->schema([
                            SchemaSection::make(__('admin/document_templates.form.sections.preview'))
                                ->schema([
                                    Placeholder::make('template_preview')
                                        ->label(__('admin/document_templates.form.fields.template_preview'))
                                        ->content(fn (callable $get): HtmlString => self::renderPreview($get))
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'prose max-w-none dark:prose-invert space-y-4']),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('documents'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin/document_templates.form.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('admin/document_templates.form.fields.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin/document_templates.form.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'invoice'  => 'success',
                        'receipt'  => 'info',
                        'quote'    => 'warning',
                        'contract' => 'danger',
                        'report'   => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('category')
                    ->label(__('admin/document_templates.form.fields.category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'financial'   => 'success',
                        'legal'       => 'danger',
                        'marketing'   => 'info',
                        'operational' => 'warning',
                        default       => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label(__('admin/document_templates.form.fields.is_active'))
                    ->boolean(),
                TextColumn::make('documents_count')
                    ->label(__('document_templates.documents_count'))
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('type')
                    ->label(__('admin/document_templates.filters.type'))
                    ->options(DocumentTemplateType::options()),
                SelectFilter::make('category')
                    ->label(__('admin/document_templates.filters.category'))
                    ->options(DocumentTemplateCategory::options()),
                TernaryFilter::make('is_active')
                    ->label(__('admin/document_templates.filters.is_active')),
            ])
            ->actions([
                TableAction::make('preview_template')
                    ->label(__('document_templates.actions.preview'))
                    ->icon('heroicon-o-eye')
                    ->modalHeading(__('document_templates.actions.preview'))
                    ->modalSubmitAction(false)
                    ->modalContent(fn (DocumentTemplate $record): HtmlString => new HtmlString($record->content))
                    ->action(function (DocumentTemplate $record): void {
                        Notification::make()
                            ->success()
                            ->title(__('document_templates.notifications.previewed'))
                            ->send();
                    }),
                TableAction::make('duplicate_template')
                    ->label(__('document_templates.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (DocumentTemplate $record): void {
                        self::duplicateTemplate($record);

                        Notification::make()
                            ->success()
                            ->title(__('document_templates.notifications.duplicated'))
                            ->send();
                    }),
                TableViewAction::make(),
                TableEditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, DocumentTemplate $record): void {
                        if (! $record->documents()->exists()) {
                            return;
                        }

                        Notification::make()
                            ->title(__('document_templates.notifications.delete_has_documents.title'))
                            ->body(__('document_templates.notifications.delete_has_documents.body'))
                            ->warning()
                            ->send();

                        $action->halt();
                    }),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableBulkAction::make('activate')
                        ->label(__('document_templates.actions.activate'))
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title(__('document_templates.notifications.activated'))
                                ->send();
                        }),
                    TableBulkAction::make('deactivate')
                        ->label(__('document_templates.actions.deactivate'))
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->success()
                                ->title(__('document_templates.notifications.deactivated'))
                                ->send();
                        }),
                    TableDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function duplicateTemplate(DocumentTemplate $template): DocumentTemplate
    {
        $duplicate = $template->replicate();

        $duplicate->name = self::generateDuplicateName($template->name);
        $duplicate->slug = self::generateUniqueSlug($template->slug);

        $duplicate->save();

        return $duplicate;
    }

    protected static function generateDuplicateName(string $name): string
    {
        $copySuffix = __('document_templates.copy_suffix');
        $baseName = sprintf('%s %s', $name, $copySuffix);
        $nextName = $baseName;
        $counter = 2;

        while (DocumentTemplate::query()->where('name', $nextName)->exists()) {
            if (str_ends_with($copySuffix, ')')) {
                $nextName = sprintf('%s %s %d)', $name, rtrim($copySuffix, ')'), $counter);
            } else {
                $nextName = sprintf('%s %s %d', $name, $copySuffix, $counter);
            }

            $counter++;
        }

        return $nextName;
    }

    protected static function generateUniqueSlug(string $slug): string
    {
        $baseSlug = Str::slug($slug . '-copy');
        $newSlug = $baseSlug;
        $suffix = 2;

        while (DocumentTemplate::query()->where('slug', $newSlug)->exists()) {
            $newSlug = sprintf('%s-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $newSlug;
    }

    /**
     * Handle getRelations functionality with proper error handling.
     */
    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDocumentTemplates::route('/'),
            'create' => Pages\CreateDocumentTemplate::route('/create'),
            'view'   => Pages\ViewDocumentTemplate::route('/{record}'),
            'edit'   => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }

    /**
     * Normalize the variables state from the form into an associative array.
     *
     * @param  array<int, array{name?: string|null, description?: string|null}>|array<string, mixed>|null $state
     * @return array<string, string>
     */
    private static function normalizeVariablesState(?array $state): array
    {
        if (empty($state)) {
            return [];
        }

        if (! array_is_list($state)) {
            $normalized = [];

            foreach ($state as $name => $description) {
                if (! (is_string($name) || is_int($name))) {
                    continue;
                }

                $nameString = (string) $name;

                if ($nameString === '') {
                    continue;
                }

                $normalized[$nameString] = is_string($description) ? $description : (string) ($description ?? '');
            }

            return $normalized;
        }

        $normalized = [];

        foreach ($state as $item) {
            if (! is_array($item)) {
                continue;
            }

            $nameRaw = $item['name'] ?? '';

            if (! is_string($nameRaw) || $nameRaw === '') {
                continue;
            }

            $descriptionRaw = $item['description'] ?? '';
            $normalized[$nameRaw] = is_string($descriptionRaw) ? $descriptionRaw : (string) $descriptionRaw;
        }

        return $normalized;
    }

    /**
     * Expand the stored variables into a repeater-friendly structure.
     *
     * @param  array<int, array{name?: string|null, description?: string|null}>|array<string, mixed>|null $state
     * @return array<int, array{name: string, description: string}>
     */
    private static function expandVariablesState(?array $state): array
    {
        if (empty($state)) {
            return [];
        }

        if (array_is_list($state)) {
            $expanded = [];

            foreach ($state as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $nameRaw = $item['name'] ?? '';
                $descriptionRaw = $item['description'] ?? '';

                $expanded[] = [
                    'name'        => is_string($nameRaw) ? $nameRaw : (string) $nameRaw,
                    'description' => is_string($descriptionRaw) ? $descriptionRaw : (string) $descriptionRaw,
                ];
            }

            return $expanded;
        }

        $expanded = [];

        foreach ($state as $name => $description) {
            if (! (is_string($name) || is_int($name))) {
                continue;
            }

            $expanded[] = [
                'name'        => (string) $name,
                'description' => is_string($description) ? $description : (string) ($description ?? ''),
            ];
        }

        return $expanded;
    }

    /**
     * Render the preview content with placeholder data.
     *
     * @param callable(string): mixed $get
     */
    private static function renderPreview(callable $get): HtmlString
    {
        $rawContent = $get('content');
        $content = is_string($rawContent) ? $rawContent : '';

        if ($content === '') {
            return new HtmlString('<em>' . e(__('filament::common.no_data')) . '</em>');
        }

        $variablesInput = $get('variables');
        $variables = is_array($variablesInput) ? self::normalizeVariablesState($variablesInput) : [];

        if ($variables === []) {
            $variables = [
                'title'       => __('admin/document_templates.form.fields.variable_name'),
                'description' => __('admin/document_templates.form.fields.variable_description'),
            ];
        }

        /** @var array<string, string> $variables */
        foreach ($variables as $key => $description) {
            $label = $description !== '' ? $description : Str::headline((string) $key);
            $replacement = '<span class="font-semibold">' . e((string) $label) . '</span>';
            $content = str_replace('{{' . (string) $key . '}}', $replacement, $content);
        }

        return new HtmlString($content);
    }
}
