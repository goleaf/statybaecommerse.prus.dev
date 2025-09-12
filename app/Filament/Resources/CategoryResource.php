<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
// use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
// use SolutionForest\TabLayoutPlugin\Components\Tabs;
final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';


    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translations.category_information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('translations.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('translations.slug'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('translations.description'))
                            ->rows(3),
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
                Section::make(__('translations.seo_information'))
                    ->components([
                        Forms\Components\TextInput::make('seo_title')
                            ->label(__('translations.seo_title'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('seo_description')
                            ->label(__('translations.seo_description'))
                            ->rows(3)
                            ->maxLength(300),
                    ])
                    ->columns(2),
                Section::make(__('translations.category_images'))
                    ->components([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('translations.category_image'))
                            ->collection('images')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                            ->maxSize(5120)
                            ->helperText(__('translations.category_image_help'))
                            ->columnSpanFull(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->label(__('translations.category_banner'))
                            ->collection('banner')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['2:1', '16:9'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(10240)
                            ->helperText(__('translations.category_banner_help'))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
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
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('translations.parent_category'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label(__('translations.visible'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('admin.models.products'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('enabled')
                    ->query(fn($query) => $query->where('is_enabled', true)),
                Tables\Filters\Filter::make('visible')
                    ->query(fn($query) => $query->where('is_visible', true)),
                Tables\Filters\Filter::make('root_categories')
                    ->query(fn($query) => $query->whereNull('parent_id')),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('create_subcategory')
                    ->label(__('translations.create_subcategory'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(fn($record) => self::getUrl('create', ['parent_id' => $record->getKey()])),
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
            ->groups([
                Tables\Grouping\Group::make('parent.name')
                    ->label(__('translations.parent_category'))
                    ->collapsible(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            CategoryResource\RelationManagers\ChildrenRelationManager::class,
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
