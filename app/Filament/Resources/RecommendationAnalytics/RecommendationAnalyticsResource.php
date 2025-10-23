<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\RecommendationAnalytics\Pages\CreateRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\EditRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\ViewRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Schemas\RecommendationAnalyticsForm;
use App\Filament\Resources\RecommendationAnalytics\Tables\RecommendationAnalyticsTable;
use App\Models\RecommendationAnalytics;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

final class RecommendationAnalyticsResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'action';

    public static function getNavigationGroup(): \Filament\Navigation\NavigationGroup|array|string|null
    {
        return 'Analytics';
    }

    public static function getNavigationLabel(): string
    {
        return __('recommendation_analytics.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recommendation_analytics.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('recommendation_analytics.model_label');
    }

    public static function form(Schema $schema): Schema   
    {
        return RecommendationAnalyticsForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return RecommendationAnalyticsTable::configure($table);
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
            'index'  => ListRecommendationAnalytics::route('/'),
            'create' => CreateRecommendationAnalytics::route('/create'),
            'view'   => ViewRecommendationAnalytics::route('/{record}'),
            'edit'   => EditRecommendationAnalytics::route('/{record}/edit'),
        ];
    }
}