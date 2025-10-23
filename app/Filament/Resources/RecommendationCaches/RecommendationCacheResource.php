<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\RecommendationCaches\Pages\CreateRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\EditRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\ListRecommendationCaches;
use App\Filament\Resources\RecommendationCaches\Pages\ViewRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Schemas\RecommendationCacheForm;
use App\Filament\Resources\RecommendationCaches\Tables\RecommendationCachesTable;
use App\Models\RecommendationCache;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class RecommendationCacheResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'cache_key';

    public static function getNavigationGroup(): \Filament\Navigation\NavigationGroup|array|string|null
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

    public static function form(Schema $schema): Schema   
    {
        return RecommendationCacheForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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