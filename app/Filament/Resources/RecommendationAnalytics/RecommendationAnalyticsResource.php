<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics;

use App\Filament\Resources\RecommendationAnalytics\Pages\CreateRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\EditRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Schemas\RecommendationAnalyticsForm;
use App\Filament\Resources\RecommendationAnalytics\Tables\RecommendationAnalyticsTable;
use App\Models\RecommendationAnalytics;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RecommendationAnalyticsResource extends Resource
{
    protected static ?string $model = RecommendationAnalytics::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RecommendationAnalyticsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
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
            'index' => ListRecommendationAnalytics::route('/'),
            'create' => CreateRecommendationAnalytics::route('/create'),
            'edit' => EditRecommendationAnalytics::route('/{record}/edit'),
        ];
    }
}
