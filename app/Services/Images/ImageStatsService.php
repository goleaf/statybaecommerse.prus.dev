<?php declare(strict_types=1);

namespace App\Services\Images;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ImageStatsService
{
    public function getImageStatistics(): array
    {
        return Cache::remember('image_statistics', 300, function () {
            $totalProducts = Product::count();
            $productsWithImages = Product::whereHas('media', function ($q) {
                $q->where('collection_name', 'images');
            })->count();

            $totalImages = Media::where('collection_name', 'images')->count();
            $generatedImages = Media::where('collection_name', 'images')
                ->whereJsonContains('custom_properties->generated', true)
                ->count();

            $webpImages = Media::where('collection_name', 'images')
                ->where('mime_type', 'image/webp')
                ->count();

            $averageImagesPerProduct = $productsWithImages > 0 ? round($totalImages / $productsWithImages, 1) : 0;

            return [
                'total_products' => $totalProducts,
                'products_with_images' => $productsWithImages,
                'products_without_images' => $totalProducts - $productsWithImages,
                'total_images' => $totalImages,
                'generated_images' => $generatedImages,
                'uploaded_images' => $totalImages - $generatedImages,
                'webp_images' => $webpImages,
                'webp_percentage' => $totalImages > 0 ? round(($webpImages / $totalImages) * 100, 1) : 0,
                'average_images_per_product' => $averageImagesPerProduct,
                'coverage_percentage' => $totalProducts > 0 ? round(($productsWithImages / $totalProducts) * 100, 1) : 0,
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget('image_statistics');
    }

    public function getTopProductsByImages(int $limit = 10): array
    {
        return Product::whereHas('media', function ($q) {
            $q->where('collection_name', 'images');
        })
            ->withCount(['media' => function ($q) {
                $q->where('collection_name', 'images');
            }])
            ->orderBy('media_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'slug'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image_count' => $product->media_count,
                    'url' => route('products.show', ['slug' => $product->slug]),
                ];
            })
            ->toArray();
    }

    public function getImageSizeBreakdown(): array
    {
        $sizes = Media::where('collection_name', 'images')
            ->selectRaw('
                COUNT(*) as total,
                AVG(size) as avg_size,
                MIN(size) as min_size,
                MAX(size) as max_size,
                SUM(size) as total_size
            ')
            ->first();

        return [
            'total_files' => $sizes->total ?? 0,
            'average_size' => $this->formatBytes($sizes->avg_size ?? 0),
            'min_size' => $this->formatBytes($sizes->min_size ?? 0),
            'max_size' => $this->formatBytes($sizes->max_size ?? 0),
            'total_storage' => $this->formatBytes($sizes->total_size ?? 0),
        ];
    }

    private function formatBytes(float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
