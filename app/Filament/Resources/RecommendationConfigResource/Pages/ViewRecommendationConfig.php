<?php declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables;

final class ViewRecommendationConfig extends ViewRecord
{
    protected static string $resource = RecommendationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
            ]);
    }
}
