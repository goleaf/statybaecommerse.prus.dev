<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\Contracts\Entities\CategoryContract;
use App\Support\Contracts\Entities\ProductContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * HandlesContentNegotiation
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait HandlesContentNegotiation
{
    /**
     * Handle content negotiation for different response formats
     */
    protected function handleContentNegotiation(Request $request, array $data, ?string $viewName = null, array $viewData = [], bool $wrap = true): JsonResponse|View|Response
    {
        $payload = $wrap
            ? ['success' => true, 'data' => $data, 'timestamp' => now()->toISOString()]
            : $data;

        // JSON response (API clients, AJAX requests)
        if ($request->accepts(['application/json', 'text/json'])) {
            return response()->json($payload);
        }
        // XML response (legacy systems, RSS feeds)
        if ($request->accepts(['application/xml', 'text/xml'])) {
            $xml = $this->arrayToXml($payload, 'response');

            return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
        }
        // CSV response (data export, spreadsheet applications)
        if ($request->accepts(['text/csv', 'application/csv'])) {
            $csv = $this->arrayToCsv($payload);

            return response($csv, 200, ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="export_'.now()->format('Y-m-d_H-i-s').'.csv"']);
        }
        // HTML response (web browsers, default)
        if ($viewName) {
            return view($viewName, array_merge($viewData, ['data' => $payload]));
        }

        // Fallback to JSON if no view specified
        return response()->json($payload);
    }

    /**
     * Handle content negotiation for product data specifically
     */
    protected function handleProductContentNegotiation(Request $request, $products, ?string $viewName = null, array $viewData = [], bool $wrap = true): JsonResponse|View|Response
    {
        $data = $this->formatProductData($products);

        return $this->handleContentNegotiation($request, $data, $viewName, $viewData, $wrap);
    }

    /**
     * Handle content negotiation for category data specifically
     */
    protected function handleCategoryContentNegotiation(Request $request, $categories, ?string $viewName = null, array $viewData = [], bool $wrap = true): JsonResponse|View|Response
    {
        $data = $this->formatCategoryData($categories);

        return $this->handleContentNegotiation($request, $data, $viewName, $viewData, $wrap);
    }

    /**
     * Return a JSON contract payload without the legacy success envelope.
     */
    protected function respondWithContract(Request $request, array $payload): JsonResponse|View|Response
    {
        return $this->handleContentNegotiation($request, $payload, wrap: false);
    }

    /**
     * Format product data for different content types
     */
    protected function formatProductData($products): array
    {
        if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $items = collect($products->items())->map(static function ($product) {
                return is_array($product) ? $product : ProductContract::fromModel($product);
            })->toArray();

            return [
                'products' => $items,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
            ];
        }
        if (is_iterable($products)) {
            return ['products' => collect($products)->map(static function ($product) {
                return is_array($product) ? $product : ProductContract::fromModel($product);
            })->toArray()];
        }

        return ['products' => []];
    }

    /**
     * Format category data for different content types
     */
    protected function formatCategoryData($categories): array
    {
        if (is_iterable($categories)) {
            return ['categories' => collect($categories)->map(static function ($category) {
                return is_array($category) ? $category : CategoryContract::fromModel($category);
            })->toArray()];
        }

        return ['categories' => []];
    }

    /**
     * Convert array to XML
     */
    protected function arrayToXml(array $data, string $rootElement = 'root'): string
    {
        $xml = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><{$rootElement}></{$rootElement}>");
        $this->arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    /**
     * Recursively convert array to XML
     */
    protected function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Convert array to CSV
     */
    protected function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }
        $output = fopen('php://temp', 'r+');
        // Flatten the data structure for CSV
        $flattened = $this->flattenArray($data);
        if (! empty($flattened)) {
            // Add headers
            fputcsv($output, array_keys($flattened[0]));
            // Add data rows
            foreach ($flattened as $row) {
                fputcsv($output, $row);
            }
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Flatten array for CSV export
     */
    protected function flattenArray(array $data): array
    {
        $flattened = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['products']) && is_array($value['products'])) {
                    // Handle products array
                    foreach ($value['products'] as $product) {
                        $flattened[] = $product;
                    }
                } elseif (isset($value['categories']) && is_array($value['categories'])) {
                    // Handle categories array
                    foreach ($value['categories'] as $category) {
                        $flattened[] = $category;
                    }
                } else {
                    // Handle other arrays
                    $flattened[] = $value;
                }
            } else {
                $flattened[] = [$key => $value];
            }
        }

        return $flattened;
    }
}
