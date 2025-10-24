<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CollectionsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Filament\Widgets\InlineCharts\ProductSalesSparkline;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Support\Authorization\AuthorizationMatrix;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\Schemas\TestingSchemaHost;
use App\Support\Forms\MatrixFactory;
use App\Support\Seo\LocaleUrlGenerator;
use App\Filament\Forms\Components\Quantity;
use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use BackedEnum;
use App\Services\Export\ExportColumn;
use App\Services\Export\Contracts\DefinesExportColumns;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\InlineChart\Tables\Columns\InlineChart;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Tapp\FilamentValueRangeFilter\Filters\ValueRangeFilter;

/**
 * ProductResource
 *
 * Filament v4 resource for Product management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductResource extends Resource implements DefinesExportColumns
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $model = Product::class;

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('products', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('products', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('products', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('products', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('products', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('products', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('products', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('products', 'update');
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-cube';
    }

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Products';
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';
    public static function getNavigationLabel(): string
    {
        return __('products.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('products.plural');
    }

    public static function getModelLabel(): string
    {
        return __('products.single');
    }

    public static function form(Schema $schema): Schema
    {
        $existingLivewire = (function () {
            return $this->livewire ?? null;
        })->call($schema);

        if ($existingLivewire === null) {
            $schema->livewire(app(TestingSchemaHost::class));
        }

        $imagesUpload = FileUpload::make('images')
            ->label(__('products.fields.images'))
            ->image()
            ->multiple()
            ->directory('products')
            ->disk('public')
            ->visibility('public')
            ->reorderable()
            ->appendFiles();

        if (method_exists($imagesUpload, 'relationship')) {
            $imagesUpload->relationship('images', 'path');
        }

        return $schema
            ->components([
                SchemaTabs::make('Product Information')
                    ->tabs([
                        SchemaTab::make('Basic Information')
                            ->components([
                                SchemaSection::make('Product Details')
                                    ->components([
                                        LanguageTabs::make([
                                            TextInput::make('name')
                                                ->label(__('products.fields.name'))
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('slug')
                                                ->label(__('products.fields.slug'))
                                                ->required()
                                                ->maxLength(255),
                                            RichEditor::make('description')
                                                ->label(__('products.fields.description'))
                                                ->toolbarButtons([
                                                    'bold',
                                                    'italic',
                                                    'underline',
                                                    'strike',
                                                    'link',
                                                    'bulletList',
                                                    'orderedList',
                                                    'grid',
                                                    'gridDelete',
                                                    'textColor',
                                                ])
                                                ->textColors([
                                                    'primary' => '#1d4ed8',
                                                    'emerald' => '#047857',
                                                    'amber'   => '#f59e0b',
                                                    'slate'   => '#475569',
                                                ]),
                                            Textarea::make('short_description')
                                                ->label(__('products.fields.short_description'))
                                                ->rows(3)
                                                ->maxLength(500),
                                        ]),
                                        SchemaGrid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('products.fields.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                                TextInput::make('slug')
                                                    ->label(__('products.fields.slug'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                                TextInput::make('barcode')
                                                    ->label(__('products.fields.barcode'))
                                                    ->maxLength(255),
                                            ]),
                                        TextInput::make('sku')
                                            ->label(__('products.fields.sku'))
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        TextInput::make('barcode')
                                            ->label(__('products.fields.barcode'))
                                            ->maxLength(255),
                                        RichEditor::make('description')
                                            ->label(__('products.fields.description'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'grid',
                                                'gridDelete',
                                                'textColor',
                                            ])
                                            ->textColors([
                                                'primary' => '#1d4ed8',
                                                'emerald' => '#047857',
                                                'amber'   => '#f59e0b',
                                                'slate'   => '#475569',
                                            ]),
                                        Textarea::make('short_description')
                                            ->label(__('products.fields.short_description'))
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                                SchemaSection::make('Pricing & Inventory')
                                    ->components([
                                        SchemaGrid::make(3)
                                            ->components([
                                                TextInput::make('price')
                                                    ->label(__('products.fields.price'))
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('compare_price')
                                                    ->label(__('products.fields.compare_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('cost_price')
                                                    ->label(__('products.fields.cost_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->components([
                                                Toggle::make('manage_stock')
                                                    ->label(__('products.fields.manage_stock'))
                                                    ->default(true),
                                                Toggle::make('track_stock')
                                                    ->label(__('products.fields.track_stock'))
                                                    ->default(true),
                                            ]),
                                        SchemaGrid::make(2)
                                            ->components([
                                                Quantity::make('stock_quantity')
                                                    ->label(__('products.fields.stock_quantity'))
                                                    ->minValue(0)
                                                    ->steps(1)
                                                    ->default(0),
                                                Quantity::make('low_stock_threshold')
                                                    ->label(__('products.fields.low_stock_threshold'))
                                                    ->minValue(0)
                                                    ->steps(1)
                                                    ->default(5),
                                            ]),
                                    ]),
                            ]),
                        SchemaTab::make('Media & SEO')
                            ->components([
                                SchemaSection::make('Product Images')
                                    ->components([
                                        $imagesUpload,
                                    ]),
                                SchemaSection::make('SEO Settings')
                                    ->components([
                                        LanguageTabs::make([
                                            TextInput::make('seo_title')
                                                ->label(__('products.fields.seo_title'))
                                                ->maxLength(255),
                                            Textarea::make('seo_description')
                                                ->label(__('products.fields.seo_description'))
                                                ->rows(3)
                                                ->maxLength(160),
                                        ]),
                                    ]),
                            ]),
                        SchemaTab::make('Settings & Options')
                            ->components([
                                SchemaSection::make('Product Settings')
                                    ->components([
                                        SchemaGrid::make(2)
                                            ->components([
                                                Select::make('brand_id')
                                                    ->label(__('products.fields.brand'))
                                                    ->relationship('brand', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                                Select::make('status')
                                                    ->label(__('products.fields.status'))
                                                    ->options([
                                                        'draft'     => __('products.status.draft'),
                                                        'published' => __('products.status.published'),
                                                        'archived'  => __('products.status.archived'),
                                                    ])
                                                    ->default('draft'),
                                            ]),
                                        SchemaGrid::make(3)
                                            ->components([
                                                Toggle::make('is_visible')
                                                    ->label(__('products.fields.is_visible'))
                                                    ->default(true),
                                                Toggle::make('is_featured')
                                                    ->label(__('products.fields.is_featured')),
                                                Toggle::make('allow_backorder')
                                                    ->label(__('products.fields.allow_backorder')),
                                            ]),
                                        SupportFlatpickr::makeDateTime('published_at')
                                            ->label(__('products.fields.published_at'))
                                            ->default(now()),
                                    ]),
                                SchemaSection::make('Physical Properties')
                                    ->components([
                                        SchemaGrid::make(4)
                                            ->components([
                                                TextInput::make('weight')
                                                    ->label(__('products.fields.weight'))
                                                    ->numeric()
                                                    ->suffix('kg')
                                                    ->step(0.01),
                                                TextInput::make('length')
                                                    ->label(__('products.fields.length'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                                TextInput::make('width')
                                                    ->label(__('products.fields.width'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                                TextInput::make('height')
                                                    ->label(__('products.fields.height'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                            ]),
                                    ]),
                            ]),
                        SchemaTab::make('Variants & Attributes')
                            ->components([
                                SchemaSection::make(__('products.sections.variant_matrix'))
                                    ->components([
                                        MatrixFactory::checkboxGrid(
                                            'variant_attribute_matrix',
                                            [
                                                'size'     => __('products.matrix.rows.size'),
                                                'color'    => __('products.matrix.rows.color'),
                                                'material' => __('products.matrix.rows.material'),
                                            ],
                                            [
                                                'primary_sku' => __('products.matrix.columns.primary'),
                                                'bundle_sku'  => __('products.matrix.columns.bundle'),
                                                'limited_sku' => __('products.matrix.columns.limited'),
                                            ],
                                            __('products.fields.variant_attribute_matrix'),
                                        )
                                            ->helperText(__('products.matrix.helper_text'))
                                            ->columnSpanFull()
                                            ->live(),
                                    ])
                                    ->columns(1),
                            ]),
                        SchemaTab::make('Advanced')
                            ->components([
                                SchemaSection::make('Additional Data')
                                    ->components([
                                        KeyValue::make('metadata')
                                            ->label(__('products.fields.metadata'))
                                            ->keyLabel(__('products.fields.metadata_key'))
                                            ->valueLabel(__('products.fields.metadata_value')),
                                        TagsInput::make('tags')
                                            ->label(__('products.fields.tags'))
                                            ->placeholder(__('products.fields.tags_placeholder')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100])
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('products.fields.image'))
                    ->circular()
                    ->size(50),
                BadgeableColumn::make('name')
                    ->label(__('products.fields.name'))
                    ->searchable(['name', 'sku', 'brand.name'])
                    ->sortable()
                    ->limit(50)
                    ->asPills()
                    ->tooltip(fn (Product $record): ?string => mb_strlen((string) $record->name) > 50 ? $record->name : null)
                    ->prefixBadges(function (Product $record): array {
                        // Surface SKU and brand context directly beside the product title for rapid identification.
                        return collect([
                            filled($record->sku)
                                ? Badge::make('sku')
                                    ->label(__('products.badges.sku', ['sku' => $record->sku]))
                                    ->color('gray')
                                    ->weight('medium')
                                : null,
                            $record->brand?->name
                                ? Badge::make('brand')
                                    ->label(__('products.badges.brand', ['brand' => $record->brand->name]))
                                    ->color('info')
                                : null,
                        ])->filter()->values()->all();
                    })
                    ->suffixBadges(function (Product $record): array {
                        // Highlight merchandising state, inventory health, and engagement metrics inline with the name.
                        $statusColor = match ($record->status) {
                            'published' => 'success',
                            'archived'  => 'warning',
                            'draft'     => 'gray',
                            default     => 'gray',
                        };

                        $approvedReviews = (int) ($record->approved_reviews_count ?? $record->reviews_count ?? 0);
                        $averageRating = $record->approved_reviews_avg_rating ?? $record->average_rating ?? null;

                        $badges = [
                            Badge::make('status')
                                ->label(__('products.status.' . $record->status))
                                ->color($statusColor),
                            Badge::make('visibility')
                                ->label($record->is_visible ? __('products.badges.visible') : __('products.badges.hidden'))
                                ->color($record->is_visible ? 'success' : 'gray'),
                        ];

                        if ($record->is_featured) {
                            $badges[] = Badge::make('featured')
                                ->label(__('products.badges.featured'))
                                ->color('warning');
                        }

                        $badges[] = Badge::make('stock')
                            ->label(__('products.badges.stock', ['count' => number_format((float) $record->stock_quantity)]))
                            ->color(match (true) {
                                $record->stock_quantity <= 0  => 'danger',
                                $record->stock_quantity <= 10 => 'warning',
                                default                       => 'success',
                            });

                        $badges[] = Badge::make('reviews')
                            ->label(__('products.badges.reviews', ['count' => number_format($approvedReviews)]))
                            ->color($approvedReviews > 0 ? 'primary' : 'gray');

                        if ($averageRating) {
                            $badges[] = Badge::make('rating')
                                ->label(__('products.badges.rating', ['rating' => number_format((float) $averageRating, 1)]))
                                ->color('info');
                        }

                        return collect($badges)->filter()->values()->all();
                    }),
                ViewColumn::make('quick_links')
                    ->label(__('Quick links'))
                    ->view('filament.tables.columns.list-group')
                    ->state(function (Product $record): array {
                        $localeUrlGenerator = app(LocaleUrlGenerator::class);
                        $locales = collect($localeUrlGenerator->supportedLocales());

                        return $locales
                            ->map(function (string $locale) use ($record, $localeUrlGenerator): ?array {
                                $slug = $record->getTranslation('slug', $locale) ?: $record->slug;

                                if (! $slug) {
                                    return null;
                                }

                                $url = $localeUrlGenerator->localizedRoute(
                                    'localized.products.show',
                                    ['product' => $slug],
                                    $locale,
                                ) ?? route('products.show', ['product' => $slug]);

                                if (! $url) {
                                    return null;
                                }

                                $name = $record->getTranslation('name', $locale) ?: $record->name;

                                return [
                                    'label' => __('Storefront (:locale): :name', [
                                        'locale' => strtoupper($locale),
                                        'name'   => $name,
                                    ]),
                                    'url'   => $url,
                                    'icon'  => 'heroicon-o-arrow-top-right-on-square',
                                    'color' => 'primary',
                                ];
                            })
                            ->filter()
                            ->values()
                            ->all();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label(__('products.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                // Inline revenue sparkline powered by the cached product series helper.
                InlineChart::make('sales_sparkline')
                    ->label(__('products.fields.sales_trend'))
                    ->chart(ProductSalesSparkline::class)
                    ->maxWidth(160)
                    ->maxHeight(48)
                    ->icon('heroicon-o-chart-bar'),
                TextColumn::make('compare_price')
                    ->label(__('products.fields.compare_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label(__('products.fields.cost_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weight')
                    ->label(__('products.fields.weight'))
                    ->suffix(' kg')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label(__('products.fields.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('products.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('product_lookup')
                    ->label(__('products.filters.product'))
                    ->form([
                        SearchableInput::make('product_name')
                            ->label(__('products.filters.product'))
                            ->placeholder(__('products.filters.product_placeholder'))
                            ->maxLength(255)
                            ->searchUsing(fn (string $search): array => self::searchProductSuggestions($search)),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $value = trim((string) ($data['product_name'] ?? ''));

                        if ($value === '') {
                            return [];
                        }

                        return [__('products.filters.product') . ': ' . $value];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $value = trim((string) ($data['product_name'] ?? ''));

                        if ($value === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($value): void {
                            $query
                                ->where('name', 'like', "%{$value}%")
                                ->orWhere('sku', 'like', "%{$value}%");
                        });
                    }),
                SelectFilter::make('brand')
                    ->label(__('products.filters.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('products.filters.status'))
                    ->options([
                        'draft'     => __('products.status.draft'),
                        'published' => __('products.status.published'),
                        'archived'  => __('products.status.archived'),
                    ]),
                TernaryFilter::make('is_visible')
                    ->label(__('products.fields.is_visible')),
                TernaryFilter::make('is_featured')
                    ->label(__('products.fields.is_featured')),
                TernaryFilter::make('manage_stock')
                    ->label(__('products.fields.manage_stock')),
                TernaryFilter::make('track_stock')
                    ->label(__('products.fields.track_stock')),
                TernaryFilter::make('allow_backorder')
                    ->label(__('products.fields.allow_backorder')),
                ValueRangeFilter::make('price')
                    ->label(__('products.fields.price'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('compare_price')
                    ->label(__('products.fields.compare_price'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('cost_price')
                    ->label(__('products.fields.cost_price'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('stock_quantity')
                    ->label(__('products.fields.stock')),
                ValueRangeFilter::make('weight')
                    ->label(__('products.fields.weight')),
                Filter::make('low_stock')
                    ->label(__('products.filters.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')),
                Filter::make('out_of_stock')
                    ->label(__('products.filters.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
                Filter::make('created_at')
                    ->form([
                        SupportFlatpickr::makeDateTime('created_from')
                            ->label(__('products.filters.created_from')),
                        SupportFlatpickr::makeDateTime('created_until')
                            ->label(__('products.filters.created_until')),
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
                TrashedFilter::make(),
            ])
            ->filtersFormWidth(Width::Large)
            ->headerActions([
                ExportAction::make()
                    ->label(__('Export'))
                    ->exports(self::getExportPresets()),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('products', 'view')),
                    EditAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    DeleteAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('products', 'delete')),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->exports(self::getExportPresets())
                        ->visible(fn () => AuthorizationMatrix::check('products', 'viewAny')),
                    BulkAction::make('publish')
                        ->label(__('products.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'published', 'is_visible' => true]);
                            Notification::make()
                                ->title(__('products.notifications.published'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    BulkAction::make('unpublish')
                        ->label(__('products.actions.unpublish'))
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'draft', 'is_visible' => false]);
                            Notification::make()
                                ->title(__('products.notifications.unpublished'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    BulkAction::make('feature')
                        ->label(__('products.actions.feature'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('products.notifications.featured'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    BulkAction::make('unfeature')
                        ->label(__('products.actions.unfeature'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('products.notifications.unfeatured'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    BulkAction::make('update_stock')
                        ->label(__('products.actions.update_stock'))
                        ->icon('heroicon-o-cube')
                        ->form([
                            TextInput::make('stock_quantity')
                                ->label(__('products.fields.stock_quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('low_stock_threshold')
                                ->label(__('products.fields.low_stock_threshold'))
                                ->numeric()
                                ->default(5),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'stock_quantity'      => $data['stock_quantity'],
                                'low_stock_threshold' => $data['low_stock_threshold'],
                            ]);
                            Notification::make()
                                ->title(__('products.notifications.stock_updated'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    BulkAction::make('update_prices')
                        ->label(__('products.actions.update_prices'))
                        ->icon('heroicon-o-currency-euro')
                        ->form([
                            TextInput::make('price_increase_percentage')
                                ->label(__('products.fields.price_increase_percentage'))
                                ->numeric()
                                ->suffix('%')
                                ->helperText(__('products.helpers.price_increase')),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $percentage = $data['price_increase_percentage'] ?? 0;
                            $multiplier = 1 + ($percentage / 100);

                            $records->each(function ($product) use ($multiplier): void {
                                $product->update([
                                    'price'         => round($product->price * $multiplier, 2),
                                    'compare_price' => $product->compare_price ? round($product->compare_price * $multiplier, 2) : null,
                                    'cost_price'    => $product->cost_price ? round($product->cost_price * $multiplier, 2) : null,
                                ]);
                            });

                            Notification::make()
                                ->title(__('products.notifications.prices_updated'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('products', 'update')),
                    DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('products', 'delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, ExportColumn>
     */
    public static function availableExportColumns(): array
    {
        return [
            'sku' => new ExportColumn(
                'sku',
                __('products.fields.sku'),
                'sku'
            ),
            'name' => new ExportColumn(
                'name',
                __('products.fields.name'),
                resolver: static fn (Product $product): string => (string) $product->getTranslation('name', app()->getLocale()) ?: (string) ($product->name ?? '')
            ),
            'status' => new ExportColumn(
                'status',
                __('products.fields.status'),
                resolver: static fn (Product $product): string => (string) ($product->status ?? '')
            ),
            'price' => new ExportColumn(
                'price',
                __('products.fields.price'),
                resolver: static fn (Product $product): string => $product->price !== null ? number_format((float) $product->price, 2, '.', '') : ''
            ),
            'stock_quantity' => new ExportColumn(
                'stock_quantity',
                __('products.fields.stock_quantity'),
                resolver: static fn (Product $product): string => (string) ($product->stock_quantity ?? 0)
            ),
            'is_visible' => new ExportColumn(
                'is_visible',
                __('products.fields.is_visible'),
                'is_visible'
            ),
            'brand' => new ExportColumn(
                'brand',
                __('products.fields.brand'),
                resolver: static fn (Product $product): string => (string) ($product->brand?->name ?? '')
            ),
            'published_at' => new ExportColumn(
                'published_at',
                __('products.fields.published_at'),
                resolver: static fn (Product $product): string => optional($product->published_at)->toDateTimeString() ?? ''
            ),
            'created_at' => new ExportColumn(
                'created_at',
                __('products.fields.created_at'),
                resolver: static fn (Product $product): string => optional($product->created_at)->toDateTimeString() ?? ''
            ),
        ];
    }

    /**
     * @return Builder<Product>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'brand:id,name',
                'primaryImage',
                'categories' => fn ($query) => $query->select([
                    'categories.id',
                    'categories.name',
                    'categories.slug',
                ])->withPivot('created_at', 'updated_at'),
                'collections' => fn ($query) => $query->select([
                    'collections.id',
                    'collections.name',
                    'collections.slug',
                ])->withPivot('created_at', 'updated_at'),
                'variants' => fn ($query) => $query->select([
                    'product_variants.id',
                    'product_variants.product_id',
                    'product_variants.sku',
                    'product_variants.name',
                    'product_variants.variant_name_lt',
                    'product_variants.variant_name_en',
                    'product_variants.price',
                    'product_variants.compare_price',
                    'product_variants.cost_price',
                    'product_variants.stock_quantity',
                    'product_variants.available_quantity',
                    'product_variants.is_default',
                    'product_variants.is_enabled',
                ]),
                'media' => fn ($query) => $query->select([
                    'media.id',
                    'media.model_type',
                    'media.model_id',
                    'media.collection_name',
                    'media.name',
                    'media.file_name',
                    'media.disk',
                    'media.conversions_disk',
                    'media.mime_type',
                    'media.size',
                    'media.custom_properties',
                    'media.generated_conversions',
                    'media.responsive_images',
                    'media.order_column',
                    'media.created_at',
                    'media.updated_at',
                ]),
            ])
            ->withCount([
                'reviews as approved_reviews_count' => fn (Builder $query): Builder => $query->where('is_approved', true),
                'categories',
                'collections',
                'variants',
                'media',
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn (Builder $query): Builder => $query->where('is_approved', true),
            ], 'rating');
    }

    /**
     * @return Builder<Product>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getModel()::query()->withoutGlobalScopes([
            ActiveScope::class,
            PublishedScope::class,
            VisibleScope::class,
        ]);
    }

    /**
     * @return array<int, ExcelExport>
     */
    protected static function getExportPresets(): array
    {
        return [
            ExcelExport::make('visible_columns')
                ->fromTable()
                ->queue()
                ->withChunkSize(500),
            ExcelExport::make('price_list_eur')
                ->only(['sku', 'name', 'price'])
                ->withColumns([
                    Column::make('sku')
                        ->heading(__('products.fields.sku')),
                    Column::make('name')
                        ->heading(__('products.fields.name')),
                    Column::make('price')
                        ->heading(__('products.fields.price'))
                        ->formatStateUsing(fn ($state): float => (float) $state)
                        ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE),
                ])
                ->queue(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CategoriesRelationManager::class,
            CollectionsRelationManager::class,
            DocumentsRelationManager::class,
            ReviewsRelationManager::class,
            VariantsRelationManager::class,
            AttributesRelationManager::class,
            ImagesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductResource\Widgets\ProductStatsWidget::class,
            \App\Filament\Resources\ProductResource\Widgets\ProductChartWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function searchProductSuggestions(string $search): array
    {
        $term = trim($search);

        if ($term === '') {
            return [];
        }

        return Product::query()
            ->select(['name', 'sku'])
            ->where(function (Builder $query) use ($term): void {
                $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(static function (Product $product): string {
                $sku = $product->sku;

                return ltrim(($sku ? "[{$sku}] " : '') . $product->name);
            })
            ->unique()
            ->values()
            ->all();
    }
}
