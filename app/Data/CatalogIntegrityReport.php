<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * Catalog integrity report summarizing catalog data quality issues.
 */
final class CatalogIntegrityReport extends Data
{
    /**
     * @param array<int, array{entity: string, locale: string, slug: string, translation_ids: array<int, int>, entity_ids: array<int, int>}>                                       $slugConflicts
     * @param array<int, array{category_ids: array<int, int>, slugs: array<int, string>}>                                                                                          $categoryCycles
     * @param array<int, array{product_id: int, product_slug: string|null, expected: array<int, string>, baseline_variant_id: int|null, variants: array<int, array<int, string>>}> $attributeGroupMismatches
     */
    public function __construct(
        public array $slugConflicts,
        public array $categoryCycles,
        public array $attributeGroupMismatches,
    ) {}

    public function hasIssues(): bool
    {
        return $this->slugConflicts !== []
            || $this->categoryCycles !== []
            || $this->attributeGroupMismatches !== [];
    }
}
