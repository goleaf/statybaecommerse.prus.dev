<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

trait HandlesContentNegotiation
{
    /**
     * Handle content negotiation for different response formats
     */
    protected function handleContentNegotiation(
        Request $request,
        array $data,
        string $viewName = null,
        array $viewData = []
    ): JsonResponse|View|Response {
        // JSON response (API clients, AJAX requests)
        if ($request->accepts(['application/json', 'text/json'])) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ]);
        }

        // XML response (legacy systems, RSS feeds)
        if ($request->accepts(['application/xml', 'text/xml'])) {
            $xml = $this->arrayToXml($data, 'response');
            return response($xml, 200, [
                'Content-Type' => 'application/xml; charset=utf-8',
            ]);
        }

        // CSV response (data export, spreadsheet applications)
        if ($request->accepts(['text/csv', 'application/csv'])) {
            $csv = $this->arrayToCsv($data);
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
            ]);
        }

        // HTML response (web browsers, default)
        if ($viewName) {
            return view($viewName, array_merge($viewData, ['data' => $data]));
        }

        // Fallback to JSON if no view specified
        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Handle content negotiation for product data specifically
     */
    protected function handleProductContentNegotiation(
        Request $request,
        $products,
        string $viewName = null,
        array $viewData = []
    ): JsonResponse|View|Response {
        $data = $this->formatProductData($products);

        return $this->handleContentNegotiation($request, $data, $viewName, $viewData);
    }

    /**
     * Handle content negotiation for category data specifically
     */
    protected function handleCategoryContentNegotiation(
        Request $request,
        $categories,
        string $viewName = null,
        array $viewData = []
    ): JsonResponse|View|Response {
        $data = $this->formatCategoryData($categories);

        return $this->handleContentNegotiation($request, $data, $viewName, $viewData);
    }

    /**
     * Format product data for different content types
     */
    protected function formatProductData($products): array
    {
        if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'products' => $products->items(),
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
            return [
                'products' => collect($products)->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'brand' => $product->brand?->name,
                        'category' => $product->category?->name,
                        'image' => $product->getFirstMediaUrl('images', 'thumb'),
                        'url' => route('product.show', $product->slug),
                        'stock_quantity' => $product->stock_quantity ?? 0,
                        'is_visible' => $product->is_visible,
                    ];
                })->toArray(),
            ];
        }

        return ['products' => []];
    }

    /**
     * Format category data for different content types
     */
    protected function formatCategoryData($categories): array
    {
        if (is_iterable($categories)) {
            return [
                'categories' => collect($categories)->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'url' => route('category.show', $category->slug),
                        'children' => $category->children ?? [],
                        'product_count' => $category->products_count ?? 0,
                    ];
                })->toArray(),
            ];
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
        
        if (!empty($flattened)) {
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
