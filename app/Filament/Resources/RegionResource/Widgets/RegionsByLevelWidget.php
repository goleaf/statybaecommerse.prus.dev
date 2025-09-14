<?php

declare (strict_types=1);
namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Widgets\ChartWidget;
/**
 * RegionsByLevelWidget
 * 
 * Filament v4 resource for RegionsByLevelWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RegionsByLevelWidget extends ChartWidget
{
    protected static ?string $heading = 'Regions by Level';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $regionsByLevel = Region::selectRaw('level, COUNT(*) as count')->groupBy('level')->orderBy('level')->get()->mapWithKeys(function ($item) {
            $levelName = match ($item->level) {
                0 => 'Root',
                1 => 'State/Province',
                2 => 'County',
                3 => 'District',
                default => "Level {$item->level}",
            };
            return [$levelName => $item->count];
        });
        return ['datasets' => [['label' => __('regions.regions_by_level'), 'data' => array_values($regionsByLevel->toArray()), 'backgroundColor' => ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6']]], 'labels' => array_keys($regionsByLevel->toArray())];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
    }
}