<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RecommendationCaches\Pages\CreateRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\EditRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\ListRecommendationCaches;
use App\Filament\Resources\RecommendationCaches\Pages\ViewRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Schemas\RecommendationCacheForm;
use App\Filament\Resources\RecommendationCaches\Tables\RecommendationCachesTable;
use App\Models\RecommendationCache;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Schemas\Schema;

final class RecommendationCacheResource extends Resource
{
    use HasNav;

    protected static ?string $model = RecommendationCache::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'cache_key';

    public static function getNavigationGroup(): ?string
    {
        // Translate enum driven grouping for the Filament sidebar.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.recommendation_caches.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.recommendation_caches.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.recommendation_caches.model_label');
    }

    public static function form(Schema $form): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return RecommendationCacheForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return RecommendationCachesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRecommendationCaches::route('/'),
            'create' => CreateRecommendationCache::route('/create'),
            'view'   => ViewRecommendationCache::route('/{record}'),
            'edit'   => EditRecommendationCache::route('/{record}/edit'),
        ];
    }
}