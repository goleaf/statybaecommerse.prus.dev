<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\BulkProductOperationsAction;
use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\MultiLanguageTabService;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'brand.name', 'categories.name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Brand' => $record->brand?->name,
            'SKU' => $record->sku,
            'Price' => '€' . number_format($record->price, 2),
            'Stock' => $record->stock_quantity,
        ];
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Product Information')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Brand::create($data)->getKey();
                            }),
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('parent_id')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Category::create($data)->getKey();
                            }),
                        Forms\Components\Textarea::make('summary')
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Pricing & Inventory')
                    ->components([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('compare_price')
                            ->label('Compare at Price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(Product::class, 'sku', ignoreRecord: true),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->numeric()
                            ->default(10)
                            ->minValue(0),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Settings')
                    ->components([
                        Forms\Components\Toggle::make('track_inventory')
                            ->label('Track Inventory')
                            ->default(true),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(4),
                // Media Section
                Forms\Components\Section::make(__('translations.media'))
                    ->components([
                        Forms\Components\Actions::make([
                            Forms\Components\Action::make('generate_images')
                                ->label(__('translations.generate_images'))
                                ->icon('heroicon-o-photo')
                                ->color('success')
                                ->action(function (Product $record) {
                                    $imageService = app(\App\Services\Images\ProductImageService::class);

                                    try {
                                        // Generate 3 random images
                                        for ($i = 0; $i < 3; $i++) {
                                            $imagePath = $imageService->generateProductImage($record);

                                            $record
                                                ->addMedia($imagePath)
                                                ->withCustomProperties([
                                                    'generated' => true,
                                                    'product_name' => $record->name,
                                                    'image_number' => $i + 1,
                                                    'alt_text' => __('translations.product_image_alt', ['name' => $record->name, 'number' => $i + 1]),
                                                ])
                                                ->usingName($record->name . ' - ' . __('translations.image') . ' ' . ($i + 1))
                                                ->usingFileName('product_' . $record->id . '_generated_' . ($i + 1) . '.webp')
                                                ->toMediaCollection('images');

                                            if (file_exists($imagePath)) {
                                                unlink($imagePath);
                                            }
                                        }

                                        \Filament\Notifications\Notification::make()
                                            ->title(__('translations.image_generated'))
                                            ->success()
                                            ->send();
                                    } catch (\Throwable $e) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Klaida generuojant paveikslėlius')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                                ->requiresConfirmation()
                                ->modalHeading(__('translations.generate_images'))
                                ->modalDescription('Ar tikrai norite sugeneruoti atsitiktinius paveikslėlius šiam produktui?')
                                ->modalSubmitActionLabel(__('translations.generate_images'))
                                ->visible(fn(?Product $record) => $record?->exists),
                        ]),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('translations.product_images'))
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(15)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->optimize('webp')
                            ->collection('images')
                            ->conversion('image-md')
                            ->helperText(__('translations.product_images') . '. ' . __('translations.webp_format') . ' ' . __('translations.image_optimization')),
                    ])
                    ->columns(1)
                    ->collapsible(),
                // Multilanguage Tabs for Translatable Content
                Tabs::make('product_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'basic_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'slug' => [
                                    'type' => 'text',
                                    'label' => __('translations.slug'),
                                    'required' => true,
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.slug_auto_generated'),
                                ],
                                'summary' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.summary'),
                                    'maxLength' => 500,
                                    'rows' => 2,
                                    'placeholder' => __('translations.product_summary_help'),
                                ],
                                'description' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.description'),
                                    'toolbar' => [
                                        'bold', 'italic', 'link', 'bulletList', 'orderedList',
                                        'h2', 'h3', 'blockquote', 'codeBlock', 'table'
                                    ],
                                ],
                            ],
                            'seo_information' => [
                                'seo_title' => [
                                    'type' => 'text',
                                    'label' => __('translations.seo_title'),
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.seo_title_help'),
                                ],
                                'seo_description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.seo_description'),
                                    'maxLength' => 300,
                                    'rows' => 3,
                                    'placeholder' => __('translations.seo_description_help'),
                                ],
                                'meta_keywords' => [
                                    'type' => 'text',
                                    'label' => __('translations.meta_keywords'),
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.meta_keywords_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('product_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('translations.image'))
                    ->collection('images')
                    ->conversion('image-sm')
                    ->defaultImageUrl('/images/placeholder-product.jpg')
                    ->circular()
                    ->size(80)
                    ->tooltip(fn(Product $record): string =>
                        $record->hasImages()
                            ? __('translations.images') . ': ' . $record->getImagesCount()
                            : __('translations.no_image')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'inactive' => 'danger',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->lowStock())
                    ->label('Low Stock'),
                Tables\Filters\Filter::make('featured')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Only'),
            ])
            ->recordActions([
                DocumentAction::make()
                    ->variables(fn(Product $record) => [
                        '$PRODUCT_NAME' => $record->name,
                        '$PRODUCT_SKU' => $record->sku,
                        '$PRODUCT_PRICE' => number_format($record->price, 2) . ' EUR',
                        '$PRODUCT_DESCRIPTION' => $record->description,
                        '$PRODUCT_BRAND' => $record->brand?->name ?? '',
                        '$PRODUCT_CATEGORY' => $record->categories->pluck('name')->join(', '),
                        '$PRODUCT_WEIGHT' => $record->weight ? $record->weight . ' kg' : '',
                    ]),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    BulkAction::make('generate_images')
                        ->label(__('translations.generate_images'))
                        ->icon('heroicon-o-photo')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $imageService = app(\App\Services\Images\ProductImageService::class);
                            $successCount = 0;
                            $errorCount = 0;

                            foreach ($records as $product) {
                                try {
                                    // Generate 2-3 random images per product
                                    $imageCount = random_int(2, 3);

                                    for ($i = 0; $i < $imageCount; $i++) {
                                        $imagePath = $imageService->generateProductImage($product);

                                        $product
                                            ->addMedia($imagePath)
                                            ->withCustomProperties([
                                                'generated' => true,
                                                'product_name' => $product->name,
                                                'image_number' => $i + 1,
                                                'alt_text' => __('translations.product_image_alt', ['name' => $product->name, 'number' => $i + 1]),
                                            ])
                                            ->usingName($product->name . ' - ' . __('translations.image') . ' ' . ($i + 1))
                                            ->usingFileName('product_' . $product->id . '_bulk_' . ($i + 1) . '.webp')
                                            ->toMediaCollection('images');

                                        if (file_exists($imagePath)) {
                                            unlink($imagePath);
                                        }
                                    }

                                    $successCount++;
                                } catch (\Throwable $e) {
                                    $errorCount++;
                                    \Illuminate\Support\Facades\Log::warning('Bulk image generation failed', [
                                        'product_id' => $product->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('translations.image_generated'))
                                ->body("Sėkmingai sugeneruota: {$successCount}, Klaidos: {$errorCount}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('translations.generate_images'))
                        ->modalDescription('Ar tikrai norite sugeneruoti atsitiktinius paveikslėlius pažymėtiems produktams?')
                        ->modalSubmitActionLabel(__('translations.generate_images')),
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['brand', 'categories']);
    }
}
