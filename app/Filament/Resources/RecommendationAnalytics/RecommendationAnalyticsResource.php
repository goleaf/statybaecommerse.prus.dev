<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\RecommendationAnalytics\Pages\CreateRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\EditRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\ListRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Pages\ViewRecommendationAnalytics;
use App\Filament\Resources\RecommendationAnalytics\Schemas\RecommendationAnalyticsForm;
use App\Filament\Resources\RecommendationAnalytics\Tables\RecommendationAnalyticsTable;
use App\Models\RecommendationAnalytics;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class RecommendationAnalyticsResource extends Resource
{
    use HasNav;

    protected static ?string $model = RecommendationAnalytics::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return RecommendationAnalyticsForm::configure($form);
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
            'index'  => ListRecommendationAnalytics::route('/'),
            'create' => CreateRecommendationAnalytics::route('/create'),
            'view'   => ViewRecommendationAnalytics::route('/{record}'),
            'edit'   => EditRecommendationAnalytics::route('/{record}/edit'),
        ];
    }
}
