<?php

namespace App\Filament\Resources\RecommendationCaches;
use App\Filament\Resources\RecommendationCaches\Pages\CreateRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\EditRecommendationCache;
use App\Filament\Resources\RecommendationCaches\Pages\ListRecommendationCaches;
use App\Filament\Resources\RecommendationCaches\Schemas\RecommendationCacheForm;
use App\Filament\Resources\RecommendationCaches\Tables\RecommendationCachesTable;
use App\Models\RecommendationCache;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class RecommendationCacheResource extends Resource
{
    protected static ?string $model = RecommendationCache::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return RecommendationCacheForm::configure($schema);
    }
    public static function table(Table $table): Table
        return RecommendationCachesTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListRecommendationCaches::route('/'),
            'create' => CreateRecommendationCache::route('/create'),
            'edit' => EditRecommendationCache::route('/{record}/edit'),
}
