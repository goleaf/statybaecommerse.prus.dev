<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Services\MultiLanguageTabService;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\TabLayoutPlugin\Schemas\TabsWidget;
use UnitEnum;

final /**
 * CollectionResource
 * 
 * Filament resource for admin panel management.
 */
class CollectionResource extends Resource
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

    public static function form(Schema $schema): Schema
    {
        return $form
            ->schema([
                // Basic Information Section
                Section::make(__('admin.collections.sections.basic_information'))
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
                Section::make(__('admin.collections.sections.collection_settings'))
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

                        Grid::make(3)
                            ->schema([
                                TextInput::make('display_type')
                                    ->label(__('admin.collections.fields.display_type'))
                                    ->helperText(__('admin.collections.help.display_type'))
                                    ->default('grid')
                                    ->options([
                                        'grid' => __('admin.collections.display_types.grid'),
                                        'list' => __('admin.collections.display_types.list'),
                                        'carousel' => __('admin.collections.display_types.carousel'),
                                    ]),

                                TextInput::make('products_per_page')
                                    ->label(__('admin.collections.fields.products_per_page'))
                                    ->helperText(__('admin.collections.help.products_per_page'))
                                    ->numeric()
                                    ->default(12)
                                    ->minValue(1)
                                    ->maxValue(100),

                                Toggle::make('show_filters')
                                    ->label(__('admin.collections.fields.show_filters'))
                                    ->helperText(__('admin.collections.help.show_filters'))
                                    ->default(true),
                            ]),
                    ]),

                // SEO Settings Section
                Section::make(__('admin.collections.sections.seo_settings'))
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

                        TextInput::make('meta_title')
                            ->label(__('admin.collections.fields.meta_title'))
                            ->placeholder(__('admin.collections.placeholders.meta_title'))
                            ->maxLength(255),

                        Textarea::make('meta_description')
                            ->label(__('admin.collections.fields.meta_description'))
                            ->placeholder(__('admin.collections.placeholders.meta_description'))
                            ->rows(2)
                            ->maxLength(500),

                        TextInput::make('meta_keywords')
                            ->label(__('admin.collections.fields.meta_keywords'))
                            ->placeholder(__('admin.collections.placeholders.meta_keywords'))
                            ->helperText(__('admin.collections.help.meta_keywords')),
                    ]),

                // Media Section
                Section::make(__('admin.collections.sections.media'))
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
                                'meta_title' => [
                                    'type' => 'text',
                                    'label' => __('admin.collections.fields.meta_title'),
                                    'maxLength' => 255,
                                ],
                                'meta_description' => [
                                    'type' => 'textarea',
                                    'label' => __('admin.collections.fields.meta_description'),
                                    'rows' => 2,
                                    'maxLength' => 500,
                                ],
                                'meta_keywords' => [
                                    'type' => 'text',
                                    'label' => __('admin.collections.fields.meta_keywords'),
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

                TextColumn::make('display_type')
                    ->label(__('admin.collections.table.display_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'grid' => 'success',
                        'list' => 'info',
                        'carousel' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("admin.collections.display_types.{$state}")),

                TextColumn::make('products_per_page')
                    ->label(__('admin.collections.table.products_per_page'))
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('show_filters')
                    ->label(__('admin.collections.table.show_filters'))
                    ->boolean()
                    ->trueIcon('heroicon-o-funnel')
                    ->falseIcon('heroicon-o-funnel-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

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

                Filter::make('display_type')
                    ->label(__('admin.collections.filters.display_type'))
                    ->form([
                        TextInput::make('display_type')
                            ->label(__('admin.collections.filters.display_type'))
                            ->options([
                                'grid' => __('admin.collections.display_types.grid'),
                                'list' => __('admin.collections.display_types.list'),
                                'carousel' => __('admin.collections.display_types.carousel'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['display_type'],
                                fn (Builder $query, $type): Builder => $query->where('display_type', $type),
                            );
                    }),

                Filter::make('show_filters')
                    ->label(__('admin.collections.filters.show_filters'))
                    ->query(fn (Builder $query): Builder => $query->where('show_filters', true)),
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
                        $record->update(['is_visible' => ! $record->is_visible]);
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
                            $record->update(['is_visible' => ! $record->is_visible]);
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

    public static function getWidgets(): array
    {
        return [
            Widgets\CollectionStatsWidget::class,
            Widgets\CollectionPerformanceWidget::class,
            Widgets\CollectionProductsWidget::class,
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
