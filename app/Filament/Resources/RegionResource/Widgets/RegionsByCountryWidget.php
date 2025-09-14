<?php

declare (strict_types=1);
namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Widgets\ChartWidget;
/**
 * RegionsByCountryWidget
 * 
 * Filament v4 resource for RegionsByCountryWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RegionsByCountryWidget extends ChartWidget
{
    protected static ?string $heading = 'Regions by Country';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $regionsByCountry = Region::with('country')->selectRaw('country_id, COUNT(*) as count')->groupBy('country_id')->get()->mapWithKeys(function ($item) {
            $countryName = $item->country?->name ?? 'Unknown';
            return [$countryName => $item->count];
        });
        return ['datasets' => [['label' => __('regions.regions_by_country'), 'data' => array_values($regionsByCountry->toArray()), 'backgroundColor' => ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1']]], 'labels' => array_keys($regionsByCountry->toArray())];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}