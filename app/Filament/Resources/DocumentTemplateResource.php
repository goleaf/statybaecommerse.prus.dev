<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Models\DocumentTemplate;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use UnitEnum;

final class DocumentTemplateResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
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
    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('document_template_form')
                ->tabs([
                    Tab::make(__('document_templates.form.tabs.basic_information'))
                        ->schema([
                            Section::make(__('document_templates.form.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('document_templates.form.fields.name'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug')
                                                ->label(__('document_templates.form.fields.slug'))
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true),
                                        ]),
                                    Textarea::make('description')
                                        ->label(__('document_templates.form.fields.description'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->columnSpanFull(),
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('document_templates.form.fields.type'))
                                                ->options(DocumentTemplateType::options())
                                                ->required()
                                                ->searchable(),
                                            Select::make('category')
                                                ->label(__('document_templates.form.fields.category'))
                                                ->options(DocumentTemplateCategory::options())
                                                ->required()
                                                ->searchable(),
                                        ]),
                                    Toggle::make('is_active')
                                        ->label(__('document_templates.form.fields.is_active'))
                                        ->default(true),
                                ]),
                        ]),
                    Textarea::make('description')
                        ->label(__('document_templates.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('document_templates.content'))
                ->components([
                    RichEditor::make('content')
                        ->label(__('document_templates.content'))
                        ->required()
                        ->columnSpanFull(),
                ]),
            Section::make(__('document_templates.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('type')
                                ->label(__('document_templates.type'))
                                ->options([
                                    'invoice'  => __('document_templates.types.invoice'),
                                    'receipt'  => __('document_templates.types.receipt'),
                                    'quote'    => __('document_templates.types.quote'),
                                    'contract' => __('document_templates.types.contract'),
                                    'report'   => __('document_templates.types.report'),
                                ])
                                ->required(),
                            Select::make('category')
                                ->label(__('document_templates.category'))
                                ->options([
                                    'financial'   => __('document_templates.categories.financial'),
                                    'legal'       => __('document_templates.categories.legal'),
                                    'marketing'   => __('document_templates.categories.marketing'),
                                    'operational' => __('document_templates.categories.operational'),
                                ])
                                ->required(),
                        ]),
                    Tab::make(__('document_templates.form.tabs.variables'))
                        ->schema([
                            Section::make(__('document_templates.form.sections.variables'))
                                ->schema([
                                    Repeater::make('variables')
                                        ->label(__('document_templates.form.fields.variables'))
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('document_templates.form.fields.variable_name'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('description')
                                                ->label(__('document_templates.form.fields.variable_description'))
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
                    Tab::make(__('document_templates.form.tabs.settings'))
                        ->schema([
                            Section::make(__('document_templates.form.sections.settings'))
                                ->schema([
                                    KeyValue::make('settings')
                                        ->label(__('document_templates.form.fields.settings'))
                                        ->keyLabel(__('document_templates.form.fields.setting_key'))
                                        ->valueLabel(__('document_templates.form.fields.setting_value'))
                                        ->addButtonLabel(__('filament-forms::components.key_value.buttons.add'))
                                        ->default([])
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make(__('document_templates.form.tabs.preview'))
                        ->schema([
                            Section::make(__('document_templates.form.sections.preview'))
                                ->schema([
                                    Placeholder::make('template_preview')
                                        ->label(__('document_templates.form.fields.template_preview'))
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
                    ->label(__('document_templates.type'))
                    ->options([
                        'invoice'  => __('document_templates.types.invoice'),
                        'receipt'  => __('document_templates.types.receipt'),
                        'quote'    => __('document_templates.types.quote'),
                        'contract' => __('document_templates.types.contract'),
                        'report'   => __('document_templates.types.report'),
                    ]),
                SelectFilter::make('category')
                    ->label(__('document_templates.category'))
                    ->options([
                        'financial'   => __('document_templates.categories.financial'),
                        'legal'       => __('document_templates.categories.legal'),
                        'marketing'   => __('document_templates.categories.marketing'),
                        'operational' => __('document_templates.categories.operational'),
                    ]),
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
                DeleteAction::make(),
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
            //
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
     */
    private static function normalizeVariablesState(?array $state): array
    {
        if (empty($state)) {
            return [];
        }

        if (! array_is_list($state)) {
            return array_filter($state, fn ($description, $name): bool => filled($name), ARRAY_FILTER_USE_BOTH);
        }

        return collect($state)
            ->filter(fn (array $item): bool => filled($item['name'] ?? null))
            ->mapWithKeys(fn (array $item): array => [
                $item['name'] => $item['description'] ?? '',
            ])
            ->all();
    }

    /**
     * Expand the stored variables into a repeater-friendly structure.
     */
    private static function expandVariablesState(?array $state): array
    {
        if (empty($state)) {
            return [];
        }

        if (array_is_list($state)) {
            return collect($state)
                ->map(fn (array $item): array => [
                    'name'        => $item['name'] ?? '',
                    'description' => $item['description'] ?? '',
                ])
                ->all();
        }

        return collect($state)
            ->map(fn ($description, $name): array => [
                'name'        => $name,
                'description' => (string) $description,
            ])
            ->values()
            ->all();
    }

    /**
     * Render the preview content with placeholder data.
     */
    private static function renderPreview(callable $get): HtmlString
    {
        $content = (string) $get('content');

        if ($content === '') {
            return new HtmlString('<em>' . e(__('filament::common.no_data')) . '</em>');
        }

        $variables = self::normalizeVariablesState($get('variables'));

        if ($variables === []) {
            $variables = [
                'title'       => __('document_templates.form.fields.variable_name'),
                'description' => __('document_templates.form.fields.variable_description'),
            ];
        }

        foreach ($variables as $key => $description) {
            $replacement = '<span class="font-semibold">' . e($description !== '' ? $description : Str::headline((string) $key)) . '</span>';
            $content = str_replace('{{' . $key . '}}', $replacement, $content);
        }

        return new HtmlString($content);
    }
}
