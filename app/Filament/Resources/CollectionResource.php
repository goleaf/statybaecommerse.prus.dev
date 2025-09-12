<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Services\MultiLanguageTabService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Schemas\TabsWidget;

final class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.collections.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.collection');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.collections');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                SchemaSection::make(__('admin.collections.sections.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.collections.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('admin.collections.placeholders.name'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }
                                        $set('slug', \Illuminate\Support\Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->label(__('admin.collections.fields.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('admin.collections.placeholders.slug'))
                                    ->helperText(__('admin.collections.help.slug'))
                                    ->unique(ignoreRecord: true),
                            ]),

                        Textarea::make('description')
                            ->label(__('admin.collections.fields.description'))
                            ->placeholder(__('admin.collections.placeholders.description'))
                            ->rows(3)
                            ->maxLength(1000),

                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('admin.collections.fields.is_visible'))
                                    ->helperText(__('admin.collections.help.is_visible'))
                                    ->default(true),

                                Toggle::make('is_automatic')
                                    ->label(__('admin.collections.fields.is_automatic'))
                                    ->helperText(__('admin.collections.help.is_automatic'))
                                    ->default(false)
                                    ->reactive(),

                                TextInput::make('sort_order')
                                    ->label(__('admin.collections.fields.sort_order'))
                                    ->helperText(__('admin.collections.help.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                    ]),

                // Collection Settings Section
                SchemaSection::make(__('admin.collections.sections.collection_settings'))
                    ->schema([
                        TextInput::make('max_products')
                            ->label(__('admin.collections.fields.max_products'))
                            ->helperText(__('admin.collections.help.max_products'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        KeyValue::make('rules')
                            ->label(__('admin.collections.fields.rules'))
                            ->helperText(__('admin.collections.help.rules'))
                            ->keyLabel(__('admin.collections.fields.rule_key'))
                            ->valueLabel(__('admin.collections.fields.rule_value'))
                            ->visible(fn (callable $get) => $get('is_automatic')),
                    ]),

                // SEO Settings Section
                SchemaSection::make(__('admin.collections.sections.seo_settings'))
                    ->schema([
                        TextInput::make('seo_title')
                            ->label(__('admin.collections.fields.seo_title'))
                            ->placeholder(__('admin.collections.placeholders.seo_title'))
                            ->maxLength(255),

                        Textarea::make('seo_description')
                            ->label(__('admin.collections.fields.seo_description'))
                            ->placeholder(__('admin.collections.placeholders.seo_description'))
                            ->rows(2)
                            ->maxLength(500),
                    ]),

                // Media Section
                SchemaSection::make(__('admin.collections.sections.media'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('image')
                                    ->label(__('admin.collections.fields.image'))
                                    ->image()
                                    ->directory('collections/images')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                        '16:9',
                                        '4:3',
                                    ]),

                                FileUpload::make('banner')
                                    ->label(__('admin.collections.fields.banner'))
                                    ->image()
                                    ->directory('collections/banners')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '21:9',
                                        '4:1',
                                    ]),
                            ]),
                    ]),

                // Multi-language Translations
                TabsWidget::make()
                    ->tabs([
                        ...MultiLanguageTabService::createSectionedTabs([
                            'basic_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('admin.collections.fields.name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('admin.collections.fields.description'),
                                    'rows' => 3,
                                    'maxLength' => 1000,
                                ],
                            ],
                            'seo_settings' => [
                                'seo_title' => [
                                    'type' => 'text',
                                    'label' => __('admin.collections.fields.seo_title'),
                                    'maxLength' => 255,
                                ],
                                'seo_description' => [
                                    'type' => 'textarea',
                                    'label' => __('admin.collections.fields.seo_description'),
                                    'rows' => 2,
                                    'maxLength' => 500,
                                ],
                            ],
                        ]),
                    ])
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('collection_tab'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('admin.collections.fields.image'))
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label(__('admin.collections.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label(__('admin.collections.table.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.copied')),

                TextColumn::make('description')
                    ->label(__('admin.collections.table.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                IconColumn::make('is_visible')
                    ->label(__('admin.collections.table.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_automatic')
                    ->label(__('admin.collections.table.is_automatic'))
                    ->boolean()
                    ->trueIcon('heroicon-o-cog-6-tooth')
                    ->falseIcon('heroicon-o-hand-raised')
                    ->trueColor('info')
                    ->falseColor('gray'),

                TextColumn::make('products_count')
                    ->label(__('admin.collections.table.products_count'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.collections.table.sort_order'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('admin.collections.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.collections.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label(__('admin.collections.filters.is_visible'))
                    ->placeholder(__('admin.collections.status.visible'))
                    ->trueLabel(__('admin.collections.status.visible'))
                    ->falseLabel(__('admin.collections.status.hidden')),

                TernaryFilter::make('is_automatic')
                    ->label(__('admin.collections.filters.is_automatic'))
                    ->placeholder(__('admin.collections.types.manual'))
                    ->trueLabel(__('admin.collections.types.automatic'))
                    ->falseLabel(__('admin.collections.types.manual')),

                Filter::make('has_products')
                    ->label(__('admin.collections.filters.has_products'))
                    ->query(fn (Builder $query): Builder => $query->has('products')),

                Filter::make('created_from')
                    ->label(__('admin.collections.filters.created_from'))
                    ->form([
                        TextInput::make('created_from')
                            ->label(__('admin.collections.filters.created_from'))
                            ->type('date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),

                Filter::make('created_until')
                    ->label(__('admin.collections.filters.created_until'))
                    ->form([
                        TextInput::make('created_until')
                            ->label(__('admin.collections.filters.created_until'))
                            ->type('date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.collections.actions.view')),

                EditAction::make()
                    ->label(__('admin.collections.actions.edit')),

                Action::make('toggle_visibility')
                    ->label(__('admin.collections.actions.toggle_visibility'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->action(function (Collection $record) {
                        $record->update(['is_visible' => !$record->is_visible]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.toggle_visibility')),

                Action::make('manage_products')
                    ->label(__('admin.collections.actions.manage_products'))
                    ->icon('heroicon-o-shopping-bag')
                    ->color('info')
                    ->url(fn (Collection $record): string => route('filament.admin.resources.collections.products', $record)),

                DeleteAction::make()
                    ->label(__('admin.collections.actions.delete')),
            ])
            ->bulkActions([
                BulkAction::make('toggle_visibility')
                    ->label(__('admin.collections.actions.toggle_visibility'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->action(function ($records) {
                        $records->each(function ($record) {
                            $record->update(['is_visible' => !$record->is_visible]);
                        });
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.toggle_visibility')),

                BulkAction::make('delete')
                    ->label(__('admin.collections.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.delete')),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\TranslationsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}