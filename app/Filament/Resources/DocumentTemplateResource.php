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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
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
                    Tab::make(__('document_templates.form.tabs.content'))
                        ->schema([
                            Section::make(__('document_templates.form.sections.content'))
                                ->schema([
                                    RichEditor::make('content')
                                        ->label(__('document_templates.form.fields.content'))
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
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
                    ->color(fn (string $state): string => DocumentTemplateType::tryFrom($state)?->color() ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => DocumentTemplateType::tryFrom($state)?->label() ?? $state),
                TextColumn::make('category')
                    ->label(__('admin/document_templates.form.fields.category'))
                    ->badge()
                    ->color(fn (string $state): string => DocumentTemplateCategory::tryFrom($state)?->color() ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => DocumentTemplateCategory::tryFrom($state)?->label() ?? $state),
                IconColumn::make('is_active')
                    ->label(__('admin/document_templates.form.fields.is_active'))
                    ->boolean(),
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
                    ->options(DocumentTemplateType::options()),
                SelectFilter::make('category')
                    ->label(__('document_templates.category'))
                    ->options(DocumentTemplateCategory::options()),
                TernaryFilter::make('is_active')
                    ->label(__('admin/document_templates.filters.is_active')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
