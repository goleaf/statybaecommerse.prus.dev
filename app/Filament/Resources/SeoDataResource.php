<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\SeoDataResource\Pages;
use BackedEnum;
use App\Filament\Resources\SeoDataResource\Widgets;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoData;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\NavigationGroup;
use UnitEnum;
/**
 * SeoDataResource
 * 
 * Filament v4 resource for SeoDataResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property int|null $navigationSort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class SeoDataResource extends Resource
{
    protected static ?string $model = SeoData::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?int $navigationSort = 15;
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Content->label();
    }
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.seo_data.title');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.models.seo_data');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.models.seo_entries');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Tabs::make('SEO Data')->tabs([Tab::make(__('admin.seo_data.fields.basic_information'))->icon('heroicon-o-information-circle')->schema([Section::make(__('admin.seo_data.fields.basic_information'))->schema([Grid::make(2)->schema([Select::make('seoable_type')->label(__('admin.seo_data.fields.seoable_type'))->options([Product::class => __('admin.models.product'), Category::class => __('admin.models.category'), Brand::class => __('admin.models.brand')])->required()->reactive()->searchable()->afterStateUpdated(fn(callable $set) => $set('seoable_id', null)), Select::make('seoable_id')->label(__('admin.seo_data.fields.seoable_name'))->options(function (callable $get) {
            $type = $get('seoable_type');
            if (!$type) {
                return [];
            }
            return match ($type) {
                Product::class => Product::query()->pluck('name', 'id'),
                Category::class => Category::query()->pluck('name', 'id'),
                Brand::class => Brand::query()->pluck('name', 'id'),
                default => [],
            };
        })->required()->searchable()->disabled(fn(callable $get) => !$get('seoable_type')), Select::make('locale')->label(__('admin.seo_data.fields.locale'))->options(['lt' => __('admin.seo_data.fields.locale_name'), 'en' => 'English'])->required()->default('lt')->searchable(), TextInput::make('canonical_url')->label(__('admin.seo_data.fields.canonical_url'))->url()->maxLength(255)->helperText(__('admin.seo_data.help.canonical_url'))])])->columns(1)]), Tab::make(__('admin.seo_data.fields.title'))->icon('heroicon-o-document-text')->schema([Section::make(__('admin.seo_data.fields.title'))->schema([TextInput::make('title')->label(__('admin.seo_data.fields.title'))->required()->maxLength(60)->helperText(__('admin.seo_data.help.title'))->live()->afterStateUpdated(function (callable $set, $state) {
            $set('title_length', mb_strlen($state ?? ''));
        }), Placeholder::make('title_length')->label(__('admin.seo_data.fields.title_length'))->content(fn(callable $get) => $get('title_length') ?? 0), Textarea::make('description')->label(__('admin.seo_data.fields.description'))->required()->maxLength(160)->rows(3)->helperText(__('admin.seo_data.help.description'))->live()->afterStateUpdated(function (callable $set, $state) {
            $set('description_length', mb_strlen($state ?? ''));
        }), Placeholder::make('description_length')->label(__('admin.seo_data.fields.description_length'))->content(fn(callable $get) => $get('description_length') ?? 0), Textarea::make('keywords')->label(__('admin.seo_data.fields.keywords'))->maxLength(255)->rows(2)->helperText(__('admin.seo_data.help.keywords'))->live()->afterStateUpdated(function (callable $set, $state) {
            $keywords = array_filter(explode(',', $state ?? ''));
            $set('keywords_count', count($keywords));
        }), Placeholder::make('keywords_count')->label(__('admin.seo_data.fields.keywords_count'))->content(fn(callable $get) => $get('keywords_count') ?? 0)])->columns(1)]), Tab::make(__('admin.seo_data.fields.meta_tags'))->icon('heroicon-o-tag')->schema([Section::make(__('admin.seo_data.fields.meta_tags'))->schema([KeyValue::make('meta_tags')->label(__('admin.seo_data.fields.meta_tags'))->keyLabel(__('admin.seo_data.meta_tags.name'))->valueLabel(__('admin.seo_data.meta_tags.value'))->helperText(__('admin.seo_data.help.meta_tags'))->addActionLabel(__('admin.actions.add'))->deleteActionLabel(__('admin.actions.delete'))->reorderable(false), Repeater::make('meta_tags_structured')->label(__('admin.seo_data.meta_tags.structured'))->schema([Select::make('type')->label(__('admin.seo_data.meta_tags.type'))->options(['og:title' => __('admin.seo_data.meta_tags.og_title'), 'og:description' => __('admin.seo_data.meta_tags.og_description'), 'og:image' => __('admin.seo_data.meta_tags.og_image'), 'og:type' => __('admin.seo_data.meta_tags.og_type'), 'og:url' => __('admin.seo_data.meta_tags.og_url'), 'twitter:card' => __('admin.seo_data.meta_tags.twitter_card'), 'twitter:title' => __('admin.seo_data.meta_tags.twitter_title'), 'twitter:description' => __('admin.seo_data.meta_tags.twitter_description'), 'twitter:image' => __('admin.seo_data.meta_tags.twitter_image'), 'author' => __('admin.seo_data.meta_tags.author'), 'publisher' => __('admin.seo_data.meta_tags.publisher'), 'copyright' => __('admin.seo_data.meta_tags.copyright'), 'language' => __('admin.seo_data.meta_tags.language'), 'geo:region' => __('admin.seo_data.meta_tags.geo_region'), 'geo:placename' => __('admin.seo_data.meta_tags.geo_placename'), 'geo:position' => __('admin.seo_data.meta_tags.geo_position'), 'ICBM' => __('admin.seo_data.meta_tags.icbm'), 'revisit-after' => __('admin.seo_data.meta_tags.revisit_after'), 'distribution' => __('admin.seo_data.meta_tags.distribution'), 'rating' => __('admin.seo_data.meta_tags.rating'), 'expires' => __('admin.seo_data.meta_tags.expires')])->required()->searchable(), TextInput::make('value')->label(__('admin.seo_data.meta_tags.value'))->required()->maxLength(255)])->columns(2)->addActionLabel(__('admin.actions.add'))->deleteActionLabel(__('admin.actions.delete'))->reorderable(false)->collapsible()])->columns(1)]), Tab::make(__('admin.seo_data.fields.structured_data'))->icon('heroicon-o-code-bracket')->schema([Section::make(__('admin.seo_data.fields.structured_data'))->schema([Select::make('structured_data_type')->label(__('admin.seo_data.structured_data.type'))->options(['Product' => __('admin.seo_data.structured_data.product'), 'Article' => __('admin.seo_data.structured_data.article'), 'Organization' => __('admin.seo_data.structured_data.organization'), 'WebPage' => __('admin.seo_data.structured_data.website'), 'BreadcrumbList' => __('admin.seo_data.structured_data.breadcrumb'), 'FAQPage' => __('admin.seo_data.structured_data.faq'), 'Review' => __('admin.seo_data.structured_data.review'), 'Event' => __('admin.seo_data.structured_data.event'), 'Recipe' => __('admin.seo_data.structured_data.recipe'), 'VideoObject' => __('admin.seo_data.structured_data.video')])->reactive()->searchable(), KeyValue::make('structured_data')->label(__('admin.seo_data.fields.structured_data'))->keyLabel(__('admin.seo_data.structured_data.property'))->valueLabel(__('admin.seo_data.structured_data.value'))->helperText(__('admin.seo_data.help.structured_data'))->addActionLabel(__('admin.actions.add'))->deleteActionLabel(__('admin.actions.delete'))->reorderable(false), RichEditor::make('structured_data_json')->label(__('admin.seo_data.fields.structured_data_json'))->helperText(__('admin.seo_data.help.structured_data_json'))->disabled()->dehydrated(false)])->columns(1)]), Tab::make(__('admin.seo_data.fields.robots'))->icon('heroicon-o-cog-6-tooth')->schema([Section::make(__('admin.seo_data.fields.robots'))->schema([Grid::make(2)->schema([Toggle::make('no_index')->label(__('admin.seo_data.fields.no_index'))->helperText(__('admin.seo_data.help.no_index')), Toggle::make('no_follow')->label(__('admin.seo_data.fields.no_follow'))->helperText(__('admin.seo_data.help.no_follow'))]), Placeholder::make('robots_directive')->label(__('admin.seo_data.fields.robots'))->content(function (callable $get) {
            $robots = [];
            if ($get('no_index')) {
                $robots[] = 'noindex';
            }
            if ($get('no_follow')) {
                $robots[] = 'nofollow';
            }
            return empty($robots) ? 'index, follow' : implode(', ', $robots);
        })])->columns(1)]), Tab::make(__('admin.seo_data.fields.analysis'))->icon('heroicon-o-chart-bar')->schema([Section::make(__('admin.seo_data.seo_analysis.title'))->schema([Placeholder::make('seo_score')->label(__('admin.seo_data.fields.seo_score'))->content(function (callable $get) {
            $score = 0;
            // Title score (40 points max)
            if ($get('title')) {
                $score += 20;
                $titleLength = mb_strlen($get('title'));
                if ($titleLength >= 30 && $titleLength <= 60) {
                    $score += 20;
                }
            }
            // Description score (30 points max)
            if ($get('description')) {
                $score += 15;
                $descLength = mb_strlen($get('description'));
                if ($descLength >= 120 && $descLength <= 160) {
                    $score += 15;
                }
            }
            // Keywords score (15 points max)
            if ($get('keywords')) {
                $score += 10;
                $keywords = array_filter(explode(',', $get('keywords')));
                if (count($keywords) >= 3 && count($keywords) <= 10) {
                    $score += 5;
                }
            }
            // Canonical URL score (10 points max)
            if ($get('canonical_url')) {
                $score += 10;
            }
            // Structured data score (5 points max)
            if ($get('structured_data')) {
                $score += 5;
            }
            return min($score, 100) . '/100';
        }), Placeholder::make('seo_recommendations')->label(__('admin.seo_data.seo_analysis.recommendations'))->content(function (callable $get) {
            $recommendations = [];
            if (!$get('title')) {
                $recommendations[] = __('admin.seo_data.validation.title_required');
            } elseif (mb_strlen($get('title')) < 30) {
                $recommendations[] = __('admin.seo_data.validation.title_max', ['max' => 30]);
            } elseif (mb_strlen($get('title')) > 60) {
                $recommendations[] = __('admin.seo_data.validation.title_max', ['max' => 60]);
            }
            if (!$get('description')) {
                $recommendations[] = __('admin.seo_data.validation.description_required');
            } elseif (mb_strlen($get('description')) < 120) {
                $recommendations[] = __('admin.seo_data.validation.description_max', ['max' => 120]);
            } elseif (mb_strlen($get('description')) > 160) {
                $recommendations[] = __('admin.seo_data.validation.description_max', ['max' => 160]);
            }
            if (!$get('keywords')) {
                $recommendations[] = __('admin.seo_data.help.keywords');
            }
            if (!$get('canonical_url')) {
                $recommendations[] = __('admin.seo_data.help.canonical_url');
            }
            return empty($recommendations) ? __('admin.seo_data.seo_score.excellent') : implode('<br>', $recommendations);
        })->html()])->columns(1)])])->columnSpanFull()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('seoable_name')->label(__('admin.seo_data.fields.seoable_name'))->searchable()->sortable()->limit(30), TextColumn::make('seoable_type_name')->label(__('admin.seo_data.fields.seoable_type'))->badge()->color(fn(string $state): string => match ($state) {
            'Product' => 'success',
            'Category' => 'info',
            'Brand' => 'warning',
            default => 'gray',
        })->sortable(), TextColumn::make('locale_name')->label(__('admin.seo_data.fields.locale'))->badge()->color(fn(string $state): string => match ($state) {
            'Lietuvių' => 'primary',
            'English' => 'secondary',
            default => 'gray',
        })->sortable(), TextColumn::make('title')->label(__('admin.seo_data.fields.title'))->searchable()->sortable()->limit(50)->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            return strlen($state) > 50 ? $state : null;
        }), TextColumn::make('title_length')->label(__('admin.seo_data.fields.title_length'))->badge()->color(fn(int $state): string => match (true) {
            $state >= 30 && $state <= 60 => 'success',
            $state > 0 => 'warning',
            default => 'danger',
        })->sortable(), TextColumn::make('description_length')->label(__('admin.seo_data.fields.description_length'))->badge()->color(fn(int $state): string => match (true) {
            $state >= 120 && $state <= 160 => 'success',
            $state > 0 => 'warning',
            default => 'danger',
        })->sortable(), TextColumn::make('keywords_count')->label(__('admin.seo_data.fields.keywords_count'))->badge()->color(fn(int $state): string => match (true) {
            $state >= 3 && $state <= 10 => 'success',
            $state > 0 => 'warning',
            default => 'danger',
        })->sortable(), TextColumn::make('seo_score')->label(__('admin.seo_data.fields.seo_score'))->badge()->color(fn(int $state): string => match (true) {
            $state >= 80 => 'success',
            $state >= 60 => 'warning',
            default => 'danger',
        })->sortable(), IconColumn::make('no_index')->label(__('admin.seo_data.fields.no_index'))->boolean()->trueIcon('heroicon-o-x-mark')->falseIcon('heroicon-o-check')->trueColor('danger')->falseColor('success'), IconColumn::make('no_follow')->label(__('admin.seo_data.fields.no_follow'))->boolean()->trueIcon('heroicon-o-x-mark')->falseIcon('heroicon-o-check')->trueColor('danger')->falseColor('success'), TextColumn::make('created_at')->label(__('admin.seo_data.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), TextColumn::make('updated_at')->label(__('admin.seo_data.fields.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('locale')->label(__('admin.seo_data.filters.locale'))->options(['lt' => 'Lietuvių', 'en' => 'English'])->searchable(), SelectFilter::make('seoable_type')->label(__('admin.seo_data.filters.seoable_type'))->options([Product::class => __('admin.models.product'), Category::class => __('admin.models.category'), Brand::class => __('admin.models.brand')])->searchable(), TernaryFilter::make('has_title')->label(__('admin.seo_data.filters.has_title'))->queries(true: fn(Builder $query) => $query->whereNotNull('title'), false: fn(Builder $query) => $query->whereNull('title')), TernaryFilter::make('has_description')->label(__('admin.seo_data.filters.has_description'))->queries(true: fn(Builder $query) => $query->whereNotNull('description'), false: fn(Builder $query) => $query->whereNull('description')), TernaryFilter::make('has_keywords')->label(__('admin.seo_data.filters.has_keywords'))->queries(true: fn(Builder $query) => $query->whereNotNull('keywords'), false: fn(Builder $query) => $query->whereNull('keywords')), TernaryFilter::make('has_canonical_url')->label(__('admin.seo_data.filters.has_canonical_url'))->queries(true: fn(Builder $query) => $query->whereNotNull('canonical_url'), false: fn(Builder $query) => $query->whereNull('canonical_url')), TernaryFilter::make('has_structured_data')->label(__('admin.seo_data.filters.has_structured_data'))->queries(true: fn(Builder $query) => $query->whereNotNull('structured_data'), false: fn(Builder $query) => $query->whereNull('structured_data')), TernaryFilter::make('no_index')->label(__('admin.seo_data.filters.no_index')), TernaryFilter::make('no_follow')->label(__('admin.seo_data.filters.no_follow')), Filter::make('seo_score_range')->label(__('admin.seo_data.filters.seo_score_range'))->form([Forms\Components\TextInput::make('seo_score_from')->label(__('admin.seo_data.filters.seo_score_from'))->numeric()->minValue(0)->maxValue(100), Forms\Components\TextInput::make('seo_score_to')->label(__('admin.seo_data.filters.seo_score_to'))->numeric()->minValue(0)->maxValue(100)])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['seo_score_from'], fn(Builder $query, $score): Builder => $query->whereRaw('
                                    (CASE 
                                        WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                                        CASE 
                                            WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                                        CASE 
                                            WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                                        CASE 
                                            WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                                        CASE 
                                            WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                                        CASE 
                                            WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                                        CASE 
                                            WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                                        CASE 
                                            WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                                    ) >= ?', [$score]))->when($data['seo_score_to'], fn(Builder $query, $score): Builder => $query->whereRaw('
                                    (CASE 
                                        WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                                        CASE 
                                            WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                                        CASE 
                                            WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                                        CASE 
                                            WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                                        CASE 
                                            WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                                        CASE 
                                            WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                                        CASE 
                                            WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                                        CASE 
                                            WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                                    ) <= ?', [$score]));
        }), DateFilter::make('created_at')->label(__('admin.seo_data.fields.created_at')), DateFilter::make('updated_at')->label(__('admin.seo_data.fields.updated_at'))])->actions([ViewAction::make(), EditAction::make(), DeleteAction::make()])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make()])])->defaultSort('created_at', 'desc');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListSeoData::route('/'), 'create' => Pages\CreateSeoData::route('/create'), 'view' => Pages\ViewSeoData::route('/{record}'), 'edit' => Pages\EditSeoData::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\SeoDataOverviewWidget::class, Widgets\SeoScoreDistributionWidget::class, Widgets\SeoDataByTypeWidget::class, Widgets\SeoOptimizationWidget::class];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}