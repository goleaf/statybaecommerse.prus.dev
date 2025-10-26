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
            // Core instruction-aligned block types.
            'similar_products'           => __('recommendation_blocks.types.similar_products'),
            'frequently_bought_together' => __('recommendation_blocks.types.frequently_bought_together'),
            'trending_products'          => __('recommendation_blocks.types.trending_products'),
            'personalized'               => __('recommendation_blocks.types.personalized'),
            'category_based'             => __('recommendation_blocks.types.category_based'),
            // Legacy identifiers remain for backwards compatibility with existing fixtures.
            'featured' => __('recommendation_blocks.types.featured'),
            'related'  => __('recommendation_blocks.types.related'),
            'similar'  => __('recommendation_blocks.types.similar'),
            'trending' => __('recommendation_blocks.types.trending'),
            'recent'   => __('recommendation_blocks.types.recent'),
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
            'top'     => __('recommendation_blocks.positions.top'),
            'bottom'  => __('recommendation_blocks.positions.bottom'),
            'sidebar' => __('recommendation_blocks.positions.sidebar'),
            'inline'  => __('recommendation_blocks.positions.inline'),
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
            fn (string $type): string => __('recommendation_blocks.tabs.' . $type),
            array_keys(self::types()),
        );
    }
}
