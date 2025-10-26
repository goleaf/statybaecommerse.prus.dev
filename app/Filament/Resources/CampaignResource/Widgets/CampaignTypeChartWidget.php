<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;

final class CampaignTypeChartWidget extends ChartWidget
{
    protected ?string $heading = 'Campaign Types Distribution';

    protected function getData(): array
    {
        // Hydrate campaign models so the accessor-derived type (with metadata fallback) is honoured.
        $campaigns = Campaign::query()
            ->select(['id', 'type', 'metadata'])
            ->get();

        // Aggregate counts by resolved type while collapsing null or empty strings into an "unknown" bucket.
        $typeCounts = $campaigns
            ->map(static function (Campaign $campaign): string {
                $resolvedType = $campaign->type ?? data_get($campaign->metadata, 'type');

                if (is_string($resolvedType) && $resolvedType !== '') {
                    return $resolvedType;
                }

                return 'unknown';
            })
            ->countBy()
            ->sortDesc();

        // Produce translated labels with a graceful fallback for unfamiliar type codes.
        $labels = $typeCounts
            ->keys()
            ->map(static function (string $type): string {
                $translationKey = "campaigns.types.{$type}";
                $label = __($translationKey);

                return $label === $translationKey ? __('campaigns.types.unknown') : $label;
            })
            ->toArray();

        return [
            'datasets' => [
                [
                    'label'           => __('campaigns.charts.campaigns_by_type'),
                    'data'            => $typeCounts->values()->toArray(),
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#10B981', // emerald
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#06B6D4', // cyan
                        '#84CC16', // lime
                        '#F97316', // orange
                        '#EC4899', // pink
                        '#6B7280', // gray
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
