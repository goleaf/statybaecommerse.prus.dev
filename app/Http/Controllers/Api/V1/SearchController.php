<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\SearchQueryData;
use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = SearchQueryData::fromRequest($request);
        $limits = $this->normalizeLimits($request->input('limits', []), $query);

        $results = $this->searchService->aggregate($query, $limits);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => [
                    'items' => $this->transformItems($results['products']['items']->values()->all()),
                    'total' => $results['products']['total'],
                ],
                'categories' => [
                    'items' => $this->transformItems($results['categories']['items']->values()->all()),
                    'total' => $results['categories']['total'],
                ],
                'brands' => [
                    'items' => $this->transformItems($results['brands']['items']->values()->all()),
                    'total' => $results['brands']['total'],
                ],
            ],
            'meta' => $results['meta'],
        ]);
    }

    private function normalizeLimits(array $limits, SearchQueryData $query): array
    {
        $normalize = static function ($value) use ($query): int {
            $value = is_numeric($value) ? (int) $value : $query->perPage();

            return (int) min(max($value, 1), SearchQueryData::MAX_PER_PAGE);
        };

        return [
            'products' => $normalize($limits['products'] ?? $query->perPage()),
            'categories' => $normalize($limits['categories'] ?? min(5, $query->perPage())),
            'brands' => $normalize($limits['brands'] ?? min(5, $query->perPage())),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function transformItems(array $items): array
    {
        return array_map(static function (array $item): array {
            if (! array_key_exists('relevance_score', $item) && array_key_exists('score', $item)) {
                $item['relevance_score'] = $item['score'];
            }

            return $item;
        }, $items);
    }
}
