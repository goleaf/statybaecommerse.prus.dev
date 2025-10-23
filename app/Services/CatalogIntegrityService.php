<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\CatalogIntegrityReport;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Translations\BrandTranslation;
use App\Models\Translations\CategoryTranslation;
use App\Models\Translations\CollectionTranslation;
use App\Models\Translations\ProductTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CatalogIntegrityService
{
    /**
     * Translation models whose slugs must be unique per locale within the catalog domain.
     *
     * @var array<class-string<Model>, array{entity: string, foreign_key: string}>
     */
    private const TRANSLATION_MODELS = [
        ProductTranslation::class => ['entity' => 'product', 'foreign_key' => 'product_id'],
        CategoryTranslation::class => ['entity' => 'category', 'foreign_key' => 'category_id'],
        BrandTranslation::class => ['entity' => 'brand', 'foreign_key' => 'brand_id'],
        CollectionTranslation::class => ['entity' => 'collection', 'foreign_key' => 'collection_id'],
    ];

    public function validate(): CatalogIntegrityReport
    {
        return new CatalogIntegrityReport(
            slugConflicts: $this->detectSlugConflicts(),
            categoryCycles: $this->detectCategoryCycles(),
            attributeGroupMismatches: $this->detectVariantAttributeGroupMismatches(),
        );
    }

    /**
     * @return array<int, array{entity: string, locale: string, slug: string, translation_ids: array<int, int>, entity_ids: array<int, int>}>
     */
    private function detectSlugConflicts(): array
    {
        $conflicts = [];

        foreach (self::TRANSLATION_MODELS as $translationClass => $meta) {
            $model = new $translationClass;
            $keyName = $model->getKeyName();
            $foreignKey = $meta['foreign_key'];

            $duplicates = $translationClass::query()
                ->selectRaw('locale, slug, COUNT(*) as aggregate')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->groupBy('locale', 'slug')
                ->having('aggregate', '>', 1)
                ->orderBy('locale')
                ->orderBy('slug')
                ->get();

            foreach ($duplicates as $duplicate) {
                $records = $translationClass::query()
                    ->select([$keyName, $foreignKey])
                    ->where('locale', $duplicate->locale)
                    ->where('slug', $duplicate->slug)
                    ->orderBy($foreignKey)
                    ->get();

                $translationIds = $records
                    ->pluck($keyName)
                    ->values()
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $entityIds = $records
                    ->pluck($foreignKey)
                    ->values()
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $conflicts[] = [
                    'entity' => $meta['entity'],
                    'locale' => (string) $duplicate->locale,
                    'slug' => (string) $duplicate->slug,
                    'translation_ids' => $translationIds,
                    'entity_ids' => $entityIds,
                ];
            }
        }

        usort($conflicts, static function (array $left, array $right): int {
            return [$left['entity'], $left['locale'], $left['slug']] <=> [$right['entity'], $right['locale'], $right['slug']];
        });

        return $conflicts;
    }

    /**
     * @return array<int, array{category_ids: array<int, int>, slugs: array<int, string>}>
     */
    private function detectCategoryCycles(): array
    {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()
            ->select(['id', 'slug', 'parent_id'])
            ->get()
            ->keyBy('id');

        $cycles = [];
        /** @var array<string, true> $recorded */
        $recorded = [];

        foreach ($categories as $category) {
            $current = $category;
            $path = [];
            $pathSeen = [];

            while ($current !== null) {
                $id = (int) $current->id;

                if (isset($pathSeen[$id])) {
                    $cycleIds = array_slice($path, (int) array_search($id, $path, true));
                    $cycleIds[] = $id;
                    $uniqueIds = $this->uniquePreservingOrder($cycleIds);
                    $normalized = $this->normalizeCycle($uniqueIds);
                    $key = implode('-', $normalized);

                    if ($key !== '' && ! isset($recorded[$key])) {
                        $recorded[$key] = true;
                        $slugs = [];
                        foreach ($normalized as $categoryId) {
                            $categoryModel = $categories->get($categoryId);
                            $slugs[] = $categoryModel instanceof Category ? (string) $categoryModel->slug : '';
                        }

                        $cycles[] = [
                            'category_ids' => $normalized,
                            'slugs' => $slugs,
                        ];
                    }

                    break;
                }

                if ($current->parent_id === null) {
                    break;
                }

                $path[] = $id;
                $pathSeen[$id] = true;

                $current = $categories->get((int) $current->parent_id);
            }
        }

        usort($cycles, static function (array $left, array $right): int {
            return ($left['category_ids'][0] ?? 0) <=> ($right['category_ids'][0] ?? 0);
        });

        return $cycles;
    }

    /**
     * @return array<int, array{product_id: int, product_slug: string|null, expected: array<int, string>, baseline_variant_id: int|null, variants: array<int, array<int, string>>}>
     */
    private function detectVariantAttributeGroupMismatches(): array
    {
        $rows = DB::table('product_variant_attributes as pva')
            ->join('product_variants as pv', 'pv.id', '=', 'pva.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('attributes as a', 'a.id', '=', 'pva.attribute_id')
            ->select([
                'p.id as product_id',
                'p.slug as product_slug',
                'pv.id as variant_id',
                'a.group_name',
            ])
            ->get();

        /** @var array<int, array{slug: string|null, variants?: array<int, array<string, true>>}> $grouped */
        $grouped = [];

        foreach ($rows as $row) {
            $groupNameRaw = $row->group_name;
            $groupName = is_string($groupNameRaw) ? trim($groupNameRaw) : '';
            if ($groupName === '') {
                continue;
            }

            $productIdRaw = $row->product_id;
            $variantIdRaw = $row->variant_id;
            if (! is_numeric($productIdRaw) || ! is_numeric($variantIdRaw)) {
                continue;
            }

            $productId = (int) $productIdRaw;
            $variantId = (int) $variantIdRaw;

            $productSlugRaw = $row->product_slug;
            $grouped[$productId]['slug'] = is_string($productSlugRaw) ? $productSlugRaw : null;
            $grouped[$productId]['variants'][$variantId][$groupName] = true;
        }

        $mismatches = [];

        foreach ($grouped as $productId => $data) {
            if (! isset($data['variants'])) {
                continue;
            }

            $variants = $data['variants'];
            if (count($variants) <= 1) {
                continue;
            }

            $productSlug = $data['slug'] ?? null;
            $normalizedSlug = is_string($productSlug) ? $productSlug : null;

            $variantIdCollection = ProductVariant::query()
                ->where('product_id', $productId)
                ->pluck('id');
            /** @var Collection<int, int|string> $variantIdCollection */
            $variantIdCollection = $variantIdCollection;
            $variantIds = $variantIdCollection
                ->map(static fn (int|string $id): int => (int) $id)
                ->all();

            foreach ($variantIds as $variantId) {
                $variants[$variantId] = $variants[$variantId] ?? [];
            }

            $normalized = [];
            $variantKeys = [];
            $frequency = [];

            foreach ($variants as $variantId => $groups) {
                $set = array_keys($groups);
                sort($set);
                $normalized[$variantId] = $set;
                $key = implode('|', $set);
                $variantKeys[$variantId] = $key;
                $frequency[$key] = ($frequency[$key] ?? 0) + 1;
            }

            if ($variantKeys === [] || $frequency === []) {
                continue;
            }

            $maxFrequency = max($frequency);
            $candidateVariantIds = [];

            foreach ($variantKeys as $variantId => $key) {
                if (($frequency[$key] ?? 0) === $maxFrequency) {
                    $candidateVariantIds[] = (int) $variantId;
                }
            }

            sort($candidateVariantIds);
            $baselineVariantId = $candidateVariantIds[0] ?? null;
            $expectedKey = $baselineVariantId !== null
                ? ($variantKeys[$baselineVariantId] ?? null)
                : null;
            $expected = ($baselineVariantId !== null && $expectedKey !== null)
                ? ($normalized[$baselineVariantId] ?? [])
                : [];

            $variantDiffs = [];
            foreach ($normalized as $variantId => $set) {
                $variantKey = $variantKeys[$variantId] ?? null;
                if ($variantKey === null) {
                    continue;
                }

                /** @var string $variantKey */
                $variantKey = $variantKey;

                if ($variantKey === $expectedKey) {
                    continue;
                }

                $variantDiffs[(int) $variantId] = $set;
            }

            if ($variantDiffs !== []) {
                ksort($variantDiffs);
                $mismatches[] = [
                    'product_id' => (int) $productId,
                    'product_slug' => $normalizedSlug,
                    'expected' => $expected,
                    'baseline_variant_id' => $baselineVariantId,
                    'variants' => $variantDiffs,
                ];
            }
        }

        usort($mismatches, static function (array $left, array $right): int {
            return $left['product_id'] <=> $right['product_id'];
        });

        return $mismatches;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function uniquePreservingOrder(array $ids): array
    {
        $unique = [];
        $seen = [];

        foreach ($ids as $id) {
            if (! isset($seen[$id])) {
                $seen[$id] = true;
                $unique[] = (int) $id;
            }
        }

        return $unique;
    }

    /**
     * Normalize a detected category cycle so that comparisons are deterministic.
     *
     * @param  array<int, int>  $cycle
     * @return array<int, int>
     */
    private function normalizeCycle(array $cycle): array
    {
        $cycle = array_values($cycle);
        if ($cycle === []) {
            return [];
        }

        $minId = min($cycle);
        $index = array_search($minId, $cycle, true);
        if ($index === false) {
            return $cycle;
        }

        return array_merge(array_slice($cycle, $index), array_slice($cycle, 0, $index));
    }
}
