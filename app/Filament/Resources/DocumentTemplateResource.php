<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use \BackedEnum;
final class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';


    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.document_templates');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('documents.template_information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('documents.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $context, $state, Forms\Set $set) =>
                                $context === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('documents.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(DocumentTemplate::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\Textarea::make('description')
                            ->label(__('documents.description'))
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label(__('documents.type'))
                            ->required()
                            ->options([
                                'invoice' => __('documents.types.invoice'),
                                'receipt' => __('documents.types.receipt'),
                                'contract' => __('documents.types.contract'),
                                'agreement' => __('documents.types.agreement'),
                                'catalog' => __('documents.types.catalog'),
                                'report' => __('documents.types.report'),
                                'certificate' => __('documents.types.certificate'),
                                'document' => __('documents.types.document'),
                            ])
                            ->default('document'),
                        Forms\Components\Select::make('category')
                            ->label(__('documents.category'))
                            ->options([
                                'sales' => __('documents.categories.sales'),
                                'marketing' => __('documents.categories.marketing'),
                                'legal' => __('documents.categories.legal'),
                                'finance' => __('documents.categories.finance'),
                                'operations' => __('documents.categories.operations'),
                                'customer_service' => __('documents.categories.customer_service'),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('documents.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),
                // Multilanguage Tabs for Template Content
                Tabs::make('template_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'template_content' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.template_name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.template_description'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                ],
                                'content' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.template_content'),
                                    'toolbar' => [
                                        'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3',
                                        'italic', 'link', 'orderedList', 'redo', 'strike', 'table', 'undo'
                                    ],
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('template_tab')
                    ->contained(false),
                \Filament\Schemas\Components\Section::make(__('documents.template_variables'))
                    ->components([
                        Forms\Components\TagsInput::make('variables')
                            ->label(__('documents.variables'))
                            ->helperText(__('documents.variables_help'))
                            ->placeholder('$CUSTOMER_NAME, $ORDER_TOTAL, $CURRENT_DATE')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
                \Filament\Schemas\Components\Section::make(__('documents.print_settings'))
                    ->components([
                        Forms\Components\KeyValue::make('settings')
                            ->label(__('documents.settings'))
                            ->keyLabel(__('documents.setting_key'))
                            ->valueLabel(__('documents.setting_value'))
                            ->addActionLabel(__('documents.add_setting'))
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('documents.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('documents.type'))
                    ->badge()
                    ->colors([
                        'success' => 'invoice',
                        'warning' => 'receipt',
                        'danger' => 'contract',
                        'info' => 'document',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('documents.category'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('documents.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('documents_count')
                    ->label(__('documents.documents_count'))
                    ->counts('documents')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('documents.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('documents.type'))
                    ->options([
                        'invoice' => __('documents.types.invoice'),
                        'receipt' => __('documents.types.receipt'),
                        'contract' => __('documents.types.contract'),
                        'agreement' => __('documents.types.agreement'),
                        'catalog' => __('documents.types.catalog'),
                        'report' => __('documents.types.report'),
                        'certificate' => __('documents.types.certificate'),
                        'document' => __('documents.types.document'),
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('documents.category'))
                    ->options([
                        'sales' => __('documents.categories.sales'),
                        'marketing' => __('documents.categories.marketing'),
                        'legal' => __('documents.categories.legal'),
                        'finance' => __('documents.categories.finance'),
                        'operations' => __('documents.categories.operations'),
                        'customer_service' => __('documents.categories.customer_service'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('documents.is_active')),
            ])
            ->recordActions([
                Actions\Action::make('preview')
                    ->label(__('documents.preview'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(function (DocumentTemplate $record) {
                        $service = app(DocumentService::class);
                        $preview = $service->previewTemplate($record);

                        return response($preview)
                            ->header('Content-Type', 'text/html')
                            ->header('X-Frame-Options', 'SAMEORIGIN');
                    })
                    ->openUrlInNewTab(),
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTemplates::route('/'),
            'create' => Pages\CreateDocumentTemplate::route('/create'),
            'view' => Pages\ViewDocumentTemplate::route('/{record}'),
            'edit' => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['documents']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'type', 'category'];
    }
}
