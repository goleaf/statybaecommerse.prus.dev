<?php

declare(strict_types=1);

namespace App\Services\ProductImages;

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Filament\ProductImageDataNormalizer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class ProductImageWriteService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(Product $product, array $payload): ProductImage
    {
        $sortOrderProvided = $this->isSortOrderProvided($payload);
        $data = ProductImageDataNormalizer::normalize($payload);
        $data['product_id'] = (int) $product->getKey();

        if (! $sortOrderProvided) {
            $data['sort_order'] = $this->nextSortOrder($product);
        }

        $image = ProductImage::query()->create($data);

        $this->syncLegacyMedia($product);

        return $image->fresh() ?? $image;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(ProductImage $image, array $payload): ProductImage
    {
        $existingPath = (string) $image->path;
        $data = ProductImageDataNormalizer::normalize($payload, forUpdate: true);

        foreach (['sort_order', 'is_default', 'is_active'] as $field) {
            if (! array_key_exists($field, $payload)) {
                unset($data[$field]);
            }
        }

        $image->update($data);

        $fresh = $image->fresh() ?? $image;
        $newPath = (string) $fresh->path;

        if ($existingPath !== '' && $existingPath !== $newPath) {
            $this->deletePathIfUnused($existingPath);
        }

        $this->syncLegacyMediaForProductId((int) $fresh->product_id);

        return $fresh;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function cloneAttach(Product $targetProduct, ProductImage $sourceImage, array $overrides = []): ProductImage
    {
        $hasImages = $targetProduct->images()->withoutGlobalScopes()->exists();

        $payload = [
            'product_id' => (int) $targetProduct->getKey(),
            'path'       => (string) ($overrides['path'] ?? $sourceImage->path),
            'alt_text'   => array_key_exists('alt_text', $overrides)
                ? $overrides['alt_text']
                : $sourceImage->alt_text,
            'sort_order' => array_key_exists('sort_order', $overrides)
                ? max(0, (int) $overrides['sort_order'])
                : $this->nextSortOrder($targetProduct),
            'is_default' => array_key_exists('is_default', $overrides)
                ? (bool) $overrides['is_default']
                : (! $hasImages),
            'is_active' => array_key_exists('is_active', $overrides)
                ? (bool) $overrides['is_active']
                : (bool) $sourceImage->is_active,
        ];

        $image = ProductImage::query()->create($payload);

        $this->syncLegacyMedia($targetProduct);

        return $image->fresh() ?? $image;
    }

    /**
     * @param  array<int, mixed>        $paths
     * @return array<int, ProductImage>
     */
    public function appendPaths(Product $product, array $paths, ?string $altText = null): array
    {
        $existingPaths = $product->images()
            ->withoutGlobalScopes()
            ->pluck('path')
            ->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')
            ->all();

        $nextSortOrder = $this->nextSortOrder($product);
        $shouldMarkDefault = empty($existingPaths);
        $created = [];

        foreach ($paths as $rawPath) {
            if (! is_scalar($rawPath)) {
                continue;
            }

            $path = trim((string) $rawPath);

            if ($path === '') {
                continue;
            }

            $storedPath = $this->resolveAttachablePath($product, $path, $nextSortOrder);

            if ($storedPath === '' || in_array($storedPath, $existingPaths, true)) {
                continue;
            }

            $image = ProductImage::query()->create([
                'product_id' => (int) $product->getKey(),
                'path'       => $storedPath,
                'alt_text'   => $altText,
                'sort_order' => $nextSortOrder,
                'is_default' => $shouldMarkDefault,
                'is_active'  => true,
            ]);

            $created[] = $image;
            $existingPaths[] = $storedPath;
            $nextSortOrder++;
            $shouldMarkDefault = false;
        }

        if ($created !== []) {
            $this->syncLegacyMedia($product);
        }

        return $created;
    }

    public function replaceWithPath(Product $product, string $path, ?string $altText = null): ?ProductImage
    {
        $normalizedPath = $this->normalizeStoragePath($path);

        if ($normalizedPath === '') {
            return null;
        }

        $existingImages = $product->images()
            ->withoutGlobalScopes()
            ->get();

        $existingPaths = $existingImages
            ->pluck('path')
            ->filter(static fn (mixed $existingPath): bool => is_string($existingPath) && $existingPath !== '')
            ->unique()
            ->values();

        if ($existingImages->isNotEmpty()) {
            $this->deleteRecordsWithoutSync($existingImages);
        }

        $image = ProductImage::query()->create([
            'product_id' => (int) $product->getKey(),
            'path'       => $normalizedPath,
            'alt_text'   => $altText,
            'sort_order' => 0,
            'is_default' => true,
            'is_active'  => true,
        ]);

        foreach ($existingPaths as $existingPath) {
            $this->deletePathIfUnused($existingPath);
        }

        $this->syncLegacyMedia($product);

        return $image->fresh() ?? $image;
    }

    public function delete(ProductImage $image): void
    {
        $path = (string) $image->path;
        $productId = (int) $image->product_id;

        $image->delete();

        $this->deletePathIfUnused($path);
        $this->syncLegacyMediaForProductId($productId);
    }

    /**
     * @param iterable<int, ProductImage> $images
     */
    public function deleteMany(iterable $images): void
    {
        if ($images instanceof EloquentCollection) {
            $records = $images;
        } elseif (is_array($images)) {
            $records = new EloquentCollection($images);
        } else {
            $records = new EloquentCollection(iterator_to_array($images));
        }

        $records = $records
            ->filter(static fn (mixed $record): bool => $record instanceof ProductImage)
            ->values();

        if ($records->isEmpty()) {
            return;
        }

        $paths = $records
            ->map(static fn (ProductImage $record): string => (string) $record->path)
            ->filter(static fn (string $path): bool => $path !== '')
            ->unique()
            ->values();

        $productIds = $this->deleteRecordsWithoutSync($records);

        foreach ($paths as $path) {
            $this->deletePathIfUnused($path);
        }

        foreach (array_unique($productIds) as $productId) {
            $this->syncLegacyMediaForProductId($productId);
        }
    }

    private function syncLegacyMediaForProductId(int $productId): void
    {
        if ($productId < 1) {
            return;
        }

        $product = Product::query()
            ->withoutGlobalScopes()
            ->find($productId);

        if (! $product instanceof Product) {
            return;
        }

        $this->syncLegacyMedia($product);
    }

    private function syncLegacyMedia(Product $product): void
    {
        if (! Schema::hasTable('media')) {
            return;
        }

        try {
            $product->clearMediaCollection('images');

            $orderedImages = $product->images()
                ->withoutGlobalScopes()
                ->orderByDesc('is_default')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($orderedImages->isEmpty()) {
                $product->clearMediaCollection('thumbnail');
                $product->clearMediaCollection('product_images');

                return;
            }

            $disk = Storage::disk('public');
            $syncable = [];

            foreach ($orderedImages as $image) {
                if (! $image instanceof ProductImage) {
                    continue;
                }

                $path = $this->normalizeStoragePath((string) $image->path);

                if (! $this->isLocalProductImagePath($path)) {
                    continue;
                }

                if (! $disk->exists($path)) {
                    continue;
                }

                $syncable[] = [
                    'path'       => $path,
                    'name'       => trim((string) ($image->alt_text ?? '')),
                    'is_default' => (bool) ($image->is_default ?? false),
                ];
            }

            if ($syncable === []) {
                return;
            }

            $product->clearMediaCollection('thumbnail');
            $product->clearMediaCollection('product_images');

            foreach ($syncable as $index => $item) {
                $path = (string) $item['path'];
                $fileName = basename($path);
                $name = (string) $item['name'];
                $label = $name !== '' ? $name : $fileName;
                $properties = [
                    'product_image_path'           => $path,
                    'mirrored_from_product_images' => true,
                    'is_default'                   => (bool) $item['is_default'],
                ];

                if ($index === 0) {
                    $product
                        ->addMediaFromDisk($path, 'public')
                        ->usingName($label)
                        ->usingFileName($fileName)
                        ->preservingOriginal()
                        ->withCustomProperties($properties)
                        ->toMediaCollection('images');

                    $product
                        ->addMediaFromDisk($path, 'public')
                        ->usingName($label)
                        ->usingFileName($fileName)
                        ->preservingOriginal()
                        ->withCustomProperties($properties)
                        ->toMediaCollection('thumbnail');
                }

                $product
                    ->addMediaFromDisk($path, 'public')
                    ->usingName($label)
                    ->usingFileName($fileName)
                    ->preservingOriginal()
                    ->withCustomProperties($properties)
                    ->toMediaCollection('product_images');
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to sync legacy media from product_images', [
                'product_id' => $product->getKey(),
                'error'      => $exception->getMessage(),
            ]);
        }
    }

    private function nextSortOrder(Product $product): int
    {
        $maxSortOrder = $product->images()
            ->withoutGlobalScopes()
            ->max('sort_order');

        return is_numeric($maxSortOrder) ? ((int) $maxSortOrder + 1) : 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isSortOrderProvided(array $payload): bool
    {
        if (! array_key_exists('sort_order', $payload)) {
            return false;
        }

        $rawSortOrder = $payload['sort_order'];

        if (is_string($rawSortOrder)) {
            return trim($rawSortOrder) !== '';
        }

        return $rawSortOrder !== null;
    }

    /**
     * @return array<int, int>
     */
    private function deleteRecordsWithoutSync(iterable $images): array
    {
        $productIds = [];

        foreach ($images as $image) {
            if (! $image instanceof ProductImage) {
                continue;
            }

            $productIds[] = (int) $image->product_id;
            $image->delete();
        }

        return $productIds;
    }

    private function resolveAttachablePath(Product $product, string $path, int $index): string
    {
        if (str_starts_with($path, 'data:')) {
            return $this->storeDataUriImage($product, $path, $index);
        }

        return $this->normalizeStoragePath($path);
    }

    private function storeDataUriImage(Product $product, string $dataUri, int $index): string
    {
        if (! preg_match('/^data:(.*?);base64,(.*)$/', $dataUri, $matches)) {
            return '';
        }

        $mime = strtolower($matches[1] ?? 'image/jpeg');
        $extension = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        $contents = base64_decode($matches[2] ?? '', true);

        if (! is_string($contents) || $contents === '') {
            return '';
        }

        $directory = 'product-images/' . $product->getKey();
        $filename = 'image-' . $index . '-' . Str::random(8) . '.' . $extension;
        $path = $directory . '/' . $filename;

        try {
            Storage::disk('public')->put($path, $contents);
        } catch (Throwable) {
            return '';
        }

        return $path;
    }

    private function deletePathIfUnused(string $path): void
    {
        if (! $this->isLocalProductImagePath($path)) {
            return;
        }

        $normalizedPath = $this->normalizeStoragePath($path);

        if ($normalizedPath === '') {
            return;
        }

        $candidatePaths = array_values(array_unique(array_filter([
            $path,
            $normalizedPath,
            '/' . ltrim($normalizedPath, '/'),
        ], static fn (string $candidate): bool => $candidate !== '')));

        if (ProductImage::query()->withoutGlobalScopes()->whereIn('path', $candidatePaths)->exists()) {
            return;
        }

        try {
            Storage::disk('public')->delete($normalizedPath);
        } catch (Throwable) {
            // Best-effort clean up, do not fail write flows on storage issues.
        }
    }

    private function isLocalProductImagePath(string $path): bool
    {
        if ($path === '' || $this->isAbsoluteUrl($path) || str_starts_with($path, 'data:')) {
            return false;
        }

        $normalized = $this->normalizeStoragePath($path);

        return $normalized !== '' && str_starts_with($normalized, 'product-images/');
    }

    private function isAbsoluteUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function normalizeStoragePath(string $path): string
    {
        $normalized = trim(str_replace('\\', '/', $path));

        if ($normalized === '') {
            return '';
        }

        if ($this->isAbsoluteUrl($normalized) || str_starts_with($normalized, 'data:')) {
            return $normalized;
        }

        $normalized = trim($normalized, '/');

        foreach (['public/', 'storage/', 'app/public/', 'app/secure-media/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = ltrim(substr($normalized, strlen($prefix)), '/');

                break;
            }
        }

        return $normalized;
    }
}
