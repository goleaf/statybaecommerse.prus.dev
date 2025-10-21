<?php

declare(strict_types=1);

namespace App\Support\Recommendations;

/**
 * Class RecommendationBlockOptions
 *
 * Centralises the recommendation block option sets so resources and pages stay aligned.
 */
final class RecommendationBlockOptions
{
    /**
     * Provide the available block types keyed by their persisted value.
     *
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            // The labels are resolved through translations to keep UI copy consistent.
            'featured' => __('recommendation_blocks.types.featured'),
            'related' => __('recommendation_blocks.types.related'),
            'similar' => __('recommendation_blocks.types.similar'),
            'trending' => __('recommendation_blocks.types.trending'),
            'recent' => __('recommendation_blocks.types.recent'),
        ];
    }

    /**
     * Provide the available block positions keyed by their persisted value.
     *
     * @return array<string, string>
     */
    public static function positions(): array
    {
        return [
            'top' => __('recommendation_blocks.positions.top'),
            'bottom' => __('recommendation_blocks.positions.bottom'),
            'sidebar' => __('recommendation_blocks.positions.sidebar'),
            'inline' => __('recommendation_blocks.positions.inline'),
        ];
    }

    /**
     * Fetch the subset of labels that should be rendered as list tabs.
     *
     * @return array<string, string>
     */
    public static function tabLabels(): array
    {
        // Tabs track the same type keys used by the select inputs to keep the UI aligned.
        return array_map(
            // Each label is resolved from dedicated translation keys for clarity in the UI.
            fn (string $type): string => __('recommendation_blocks.tabs.'.$type),
            array_keys(self::types()),
        );
    }
}
