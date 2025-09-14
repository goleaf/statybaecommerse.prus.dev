<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use App\Enums\NavigationIcon;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Filament\Resources\CategoryResource\Widgets;
use App\Models\Category;
use Filament\Forms;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
/**
 * CategoryResource
 * 
 * Filament v4 resource for CategoryResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = NavigationIcon::RectangleStack;
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('categories.title');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('categories.plural');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('categories.single');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Tabs::make(__('categories.tabs.translations'))->tabs([Tab::make(__('categories.tabs.lithuanian'))->icon('heroicon-o-language')->schema([Section::make(__('categories.sections.basic_information'))->schema([TextInput::make('translations.name.lt')->label(__('categories.fields.name'))->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
            if ($operation !== 'create') {
                return;
            }
            $set('slug', \Illuminate\Support\Str::slug($state));
        }), RichEditor::make('translations.description.lt')->label(__('categories.fields.description'))->columnSpanFull(), Textarea::make('translations.short_description.lt')->label(__('categories.fields.short_description'))->rows(3)->columnSpanFull(), TextInput::make('translations.seo_title.lt')->label(__('categories.fields.seo_title'))->maxLength(255)->columnSpanFull(), Textarea::make('translations.seo_description.lt')->label(__('categories.fields.seo_description'))->rows(3)->maxLength(500)->columnSpanFull(), TextInput::make('translations.seo_keywords.lt')->label(__('categories.fields.seo_keywords'))->maxLength(255)->columnSpanFull()])->columns(2)]), Tab::make(__('categories.tabs.english'))->icon('heroicon-o-language')->schema([Section::make(__('categories.sections.basic_information'))->schema([TextInput::make('translations.name.en')->label(__('categories.fields.name'))->maxLength(255), RichEditor::make('translations.description.en')->label(__('categories.fields.description'))->columnSpanFull(), Textarea::make('translations.short_description.en')->label(__('categories.fields.short_description'))->rows(3)->columnSpanFull(), TextInput::make('translations.seo_title.en')->label(__('categories.fields.seo_title'))->maxLength(255)->columnSpanFull(), Textarea::make('translations.seo_description.en')->label(__('categories.fields.seo_description'))->rows(3)->maxLength(500)->columnSpanFull(), TextInput::make('translations.seo_keywords.en')->label(__('categories.fields.seo_keywords'))->maxLength(255)->columnSpanFull()])->columns(2)])])->columnSpanFull(), Section::make(__('categories.sections.settings'))->schema([TextInput::make('slug')->label(__('categories.fields.slug'))->required()->maxLength(255)->unique(Category::class, 'slug', ignoreRecord: true)->rules(['alpha_dash']), Select::make('parent_id')->label(__('categories.fields.parent'))->relationship('parent', 'name')->searchable()->preload()->createOptionForm([TextInput::make('name')->required()->maxLength(255), TextInput::make('slug')->required()->maxLength(255)]), TextInput::make('sort_order')->label(__('categories.fields.sort_order'))->numeric()->default(0), Toggle::make('is_enabled')->label(__('categories.fields.is_enabled'))->default(true), Toggle::make('is_featured')->label(__('categories.fields.is_featured'))->default(false), Toggle::make('is_visible')->label(__('categories.fields.is_visible'))->default(true), Toggle::make('show_in_menu')->label(__('categories.fields.show_in_menu'))->default(true), TextInput::make('product_limit')->label(__('categories.fields.product_limit'))->numeric()->default(20)])->columns(2), Section::make(__('categories.sections.media'))->schema([SpatieMediaLibraryFileUpload::make('image')->label(__('categories.fields.image'))->image()->imageEditor()->imageEditorAspectRatios(['1:1', '16:9', '4:3'])->collection('image')->columnSpanFull(), SpatieMediaLibraryFileUpload::make('banner')->label(__('categories.fields.banner'))->image()->imageEditor()->imageEditorAspectRatios(['16:9', '21:9', '4:3'])->collection('banner')->columnSpanFull(), SpatieMediaLibraryFileUpload::make('gallery')->label(__('categories.fields.gallery'))->image()->multiple()->imageEditor()->collection('gallery')->columnSpanFull()]), Section::make(__('categories.sections.hierarchy'))->schema([Repeater::make('children')->label(__('categories.fields.children'))->relationship('children')->schema([TextInput::make('name')->required()->maxLength(255), TextInput::make('slug')->required()->maxLength(255), TextInput::make('sort_order')->numeric()->default(0)])->columns(3)->collapsible()->itemLabel(fn(array $state): ?string => $state['name'] ?? null)])->collapsible()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([ImageColumn::make('image')->label(__('categories.fields.image'))->circular()->size(40)->defaultImageUrl('/images/placeholder-category.png'), TextColumn::make('name')->label(__('categories.fields.name'))->searchable()->sortable()->weight(FontWeight::Medium)->limit(50)->getStateUsing(function (Category $record): string {
            return ($record->trans('name', app()->getLocale()) ?: $record->trans('name', 'lt')) ?: 'N/A';
        }), TextColumn::make('slug')->label(__('categories.fields.slug'))->searchable()->sortable()->copyable()->copyMessage(__('admin.common.copied')), TextColumn::make('parent.name')->label(__('categories.fields.parent'))->searchable()->sortable()->badge()->color('info')->getStateUsing(function (Category $record): ?string {
            if (!$record->parent) {
                return null;
            }
            return $record->parent->trans('name', app()->getLocale()) ?: $record->parent->trans('name', 'lt');
        }), TextColumn::make('children_count')->label(__('categories.fields.children_count'))->counts('children')->sortable()->badge()->color('success'), TextColumn::make('products_count')->label(__('categories.fields.products_count'))->counts('products')->sortable()->badge()->color('warning'), TextColumn::make('sort_order')->label(__('categories.fields.sort_order'))->sortable()->badge()->color('gray'), IconColumn::make('is_enabled')->label(__('categories.fields.is_enabled'))->boolean()->sortable(), IconColumn::make('is_featured')->label(__('categories.fields.is_featured'))->boolean()->sortable(), IconColumn::make('is_visible')->label(__('categories.fields.is_visible'))->boolean()->sortable(), IconColumn::make('show_in_menu')->label(__('categories.fields.show_in_menu'))->boolean()->sortable(), TextColumn::make('product_limit')->label(__('categories.fields.product_limit'))->sortable()->badge()->color('info'), TextColumn::make('created_at')->label(__('categories.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('categories.fields.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([TernaryFilter::make('is_enabled')->label(__('categories.filters.is_enabled'))->placeholder(__('admin.common.all'))->trueLabel(__('admin.common.enabled'))->falseLabel(__('admin.common.disabled')), TernaryFilter::make('is_featured')->label(__('categories.filters.is_featured'))->placeholder(__('admin.common.all'))->trueLabel(__('admin.common.featured'))->falseLabel(__('admin.common.not_featured')), TernaryFilter::make('is_visible')->label(__('categories.filters.is_visible'))->placeholder(__('admin.common.all'))->trueLabel(__('admin.common.visible'))->falseLabel(__('admin.common.hidden')), TernaryFilter::make('show_in_menu')->label(__('categories.filters.show_in_menu'))->placeholder(__('admin.common.all'))->trueLabel(__('admin.common.yes'))->falseLabel(__('admin.common.no')), SelectFilter::make('parent_id')->label(__('categories.filters.parent'))->relationship('parent', 'name')->searchable()->preload(), SelectFilter::make('has_children')->label(__('categories.filters.has_children'))->options(['with_children' => __('categories.filters.with_children'), 'without_children' => __('categories.filters.without_children')])->query(function (Builder $query, array $data): Builder {
            return match ($data['value'] ?? null) {
                'with_children' => $query->whereHas('children'),
                'without_children' => $query->whereDoesntHave('children'),
                default => $query,
            };
        }), SelectFilter::make('has_products')->label(__('categories.filters.has_products'))->options(['with_products' => __('categories.filters.with_products'), 'without_products' => __('categories.filters.without_products')])->query(function (Builder $query, array $data): Builder {
            return match ($data['value'] ?? null) {
                'with_products' => $query->whereHas('products'),
                'without_products' => $query->whereDoesntHave('products'),
                default => $query,
            };
        }), SelectFilter::make('products_count_range')->label(__('categories.filters.products_count_range'))->options(['0' => __('categories.filters.no_products'), '1-10' => __('categories.filters.1_to_10_products'), '11-50' => __('categories.filters.11_to_50_products'), '51-100' => __('categories.filters.51_to_100_products'), '100+' => __('categories.filters.100_plus_products')])->query(function (Builder $query, array $data): Builder {
            return match ($data['value'] ?? null) {
                '0' => $query->whereDoesntHave('products'),
                '1-10' => $query->has('products', '>=', 1)->has('products', '<=', 10),
                '11-50' => $query->has('products', '>=', 11)->has('products', '<=', 50),
                '51-100' => $query->has('products', '>=', 51)->has('products', '<=', 100),
                '100+' => $query->has('products', '>', 100),
                default => $query,
            };
        }), Filter::make('created_at')->form([Forms\Components\DatePicker::make('created_from')->label(__('categories.filters.created_from')), Forms\Components\DatePicker::make('created_until')->label(__('categories.filters.created_until'))])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
        }), Filter::make('has_seo')->label(__('categories.filters.has_seo'))->query(fn(Builder $query): Builder => $query->where(function ($q) {
            $q->whereNotNull('seo_title')->where('seo_title', '!=', '')->orWhere(function ($q2) {
                $q2->whereNotNull('seo_description')->where('seo_description', '!=', '');
            });
        })), Filter::make('root_categories')->label(__('categories.filters.root_categories'))->query(fn(Builder $query): Builder => $query->whereNull('parent_id')), TrashedFilter::make()])->actions([ActionGroup::make([ViewAction::make(), EditAction::make(), Action::make('translate')->label(__('categories.actions.translate'))->icon('heroicon-o-language')->color('info')->url(fn(Category $record): string => route('filament.admin.resources.categories.edit', ['record' => $record]) . '?tab=translations')->openUrlInNewTab(false), Action::make('view_products')->label(__('categories.actions.view_products'))->icon('heroicon-o-shopping-bag')->color('warning')->url(fn(Category $record): string => route('filament.admin.resources.products.index', ['tableFilters[category_id][value]' => $record->id]))->openUrlInNewTab(false), Action::make('duplicate')->label(__('categories.actions.duplicate'))->icon('heroicon-o-document-duplicate')->color('gray')->action(function (Category $record) {
            $newCategory = $record->replicate();
            $newCategory->name = $record->trans('name') . ' (Copy)';
            $newCategory->slug = $record->slug . '-copy';
            $newCategory->save();
            Notification::make()->title(__('categories.messages.created'))->success()->send();
        }), DeleteAction::make(), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])])->bulkActions([BulkActionGroup::make([Action::make('enable_selected')->label(__('categories.bulk_actions.enable_selected'))->icon('heroicon-o-check-circle')->color('success')->action(function (Collection $records) {
            $records->each->update(['is_enabled' => true]);
            Notification::make()->title(__('categories.messages.status_changed'))->success()->send();
        }), Action::make('disable_selected')->label(__('categories.bulk_actions.disable_selected'))->icon('heroicon-o-x-circle')->color('danger')->action(function (Collection $records) {
            $records->each->update(['is_enabled' => false]);
            Notification::make()->title(__('categories.messages.status_changed'))->success()->send();
        }), Action::make('feature_selected')->label(__('categories.bulk_actions.feature_selected'))->icon('heroicon-o-star')->color('warning')->action(function (Collection $records) {
            $records->each->update(['is_featured' => true]);
            Notification::make()->title(__('categories.messages.featured_toggled'))->success()->send();
        }), DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make()])])->defaultSort('sort_order')->poll('30s')->emptyStateHeading(__('categories.messages.no_categories_found'))->emptyStateDescription(__('categories.help.create_first_category'))->emptyStateIcon('heroicon-o-rectangle-stack');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [RelationManagers\ProductsRelationManager::class, RelationManagers\ChildrenRelationManager::class, RelationManagers\TranslationsRelationManager::class];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\CategoryStatsWidget::class, Widgets\CategoryTreeWidget::class, Widgets\TopCategoriesWidget::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListCategories::route('/'), 'create' => Pages\CreateCategory::route('/create'), 'view' => Pages\ViewCategory::route('/{record}'), 'edit' => Pages\EditCategory::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
    /**
     * Handle getGlobalSearchEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['parent', 'children', 'products', 'translations']);
    }
    /**
     * Handle getGloballySearchableAttributes functionality with proper error handling.
     * @return array
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug', 'translations.name', 'translations.description', 'translations.seo_title', 'translations.seo_keywords'];
    }
    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     * @param mixed $record
     * @return array
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [__('categories.fields.parent') => $record->parent?->trans('name', app()->getLocale()) ?: $record->parent?->trans('name', 'lt'), __('categories.fields.products_count') => $record->products_count, __('categories.fields.children_count') => $record->children_count];
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return self::getModel()::count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}