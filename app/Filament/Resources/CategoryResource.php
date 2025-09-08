<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Services\MultiLanguageTabService;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Category Information (Non-translatable)
                Forms\Components\Section::make(__('translations.category_information'))
                    ->components([
                        Forms\Components\Select::make('parent_id')
                            ->label(__('translations.parent_category'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('translations.enabled'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('translations.visible'))
                            ->default(true),
                    ])
                    ->columns(2),
                // Category Images Section
                Forms\Components\Section::make(__('translations.category_images'))
                    ->components([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('translations.category_image'))
                            ->collection('images')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                            ->maxSize(5120)  // 5MB
                            ->helperText(__('translations.category_image_help'))
                            ->columnSpanFull(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->label(__('translations.category_banner'))
                            ->collection('banner')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['2:1', '16:9'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(10240)  // 10MB
                            ->helperText(__('translations.category_banner_help'))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                // Multilanguage Tabs for Translatable Content
                Tabs::make('category_translations')
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
                                'description' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.description'),
                                    'toolbar' => ['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3'],
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
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('category_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label('Image')
                    ->collection('images')
                    ->conversion('image-sm')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('Enabled')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label('Visible')
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('enabled')
                    ->query(fn($query) => $query->where('is_enabled', true)),
                Tables\Filters\Filter::make('visible')
                    ->query(fn($query) => $query->where('is_visible', true)),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
