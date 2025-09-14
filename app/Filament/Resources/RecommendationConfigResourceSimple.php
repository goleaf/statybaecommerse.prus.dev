<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\RecommendationConfig;
use BackedEnum;
use App\Enums\NavigationGroup;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;

final class RecommendationConfigResourceSimple extends Resource
{
    protected static ?string $model = RecommendationConfig::class;

    /** @var BackedEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\RecommendationConfigResourceSimple\Pages\ListRecommendationConfigResourceSimples::route('/'),
        ];
    }
}
