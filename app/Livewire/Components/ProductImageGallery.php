<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\VariantImage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ProductImageGallery
 *
 * Livewire component for ProductImageGallery with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property int $currentImageIndex
 * @property bool $showLightbox
 * @property string $imageSize
 */
final class ProductImageGallery extends Component
{
    public Product $product;

    public int $currentImageIndex = 0;

    public bool $showLightbox = false;

    public string $imageSize = 'lg';

    public ?int $activeVariantId = null;

    /**
     * Cached gallery images for the base product.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $productImages = [];

    /**
     * Variant specific image cache keyed by variant ID.
     *
     * @var array<int, array<int, array<string, mixed>>>
     */
    public array $variantImageCache = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product, string $imageSize = 'lg'): void
    {
        $this->product = $product;
        $this->imageSize = $imageSize;
        $this->productImages = $product->getGalleryImages();
    }

    /**
     * Handle images functionality with proper error handling.
     */
    #[Computed]
    public function images(): array
    {
        if ($this->activeVariantId) {
            $variantImages = $this->variantImageCache[$this->activeVariantId]
                ?? $this->loadVariantImages($this->activeVariantId);

            if (! empty($variantImages)) {
                return $variantImages;
            }
        }

        if (empty($this->productImages)) {
            $this->productImages = $this->product->getGalleryImages();
        }

        return $this->productImages;
    }

    /**
     * Handle hasImages functionality with proper error handling.
     */
    #[Computed]
    public function hasImages(): bool
    {
        return $this->product->hasImages();
    }

    /**
     * Handle currentImage functionality with proper error handling.
     */
    #[Computed]
    public function currentImage(): ?array
    {
        return $this->images[$this->currentImageIndex] ?? null;
    }

    /**
     * Handle nextImage functionality with proper error handling.
     */
    public function nextImage(): void
    {
        $total = is_countable($this->images) ? count($this->images) : 0;
        $this->currentImageIndex = $this->currentImageIndex < $total - 1 ? $this->currentImageIndex + 1 : 0;
    }

    /**
     * Handle previousImage functionality with proper error handling.
     */
    public function previousImage(): void
    {
        $total = is_countable($this->images) ? count($this->images) : 0;
        $this->currentImageIndex = $this->currentImageIndex > 0 ? $this->currentImageIndex - 1 : $total - 1;
    }

    /**
     * Handle selectImage functionality with proper error handling.
     */
    public function selectImage(int $index): void
    {
        if (isset($this->images[$index])) {
            $this->currentImageIndex = $index;
        }
    }

    /**
     * Handle toggleLightbox functionality with proper error handling.
     */
    public function toggleLightbox(): void
    {
        $this->showLightbox = ! $this->showLightbox;
    }

    #[On('variant.selected')]
    public function updateForVariant(?int $variantId): void
    {
        $this->activeVariantId = $variantId;
        $this->currentImageIndex = 0;
    }

    protected function loadVariantImages(int $variantId): array
    {
        $variant = $this->product
            ->variants()
            ->with(['images' => fn ($query) => $query->orderBy('position')])
            ->find($variantId);

        if (! $variant) {
            return [];
        }

        $images = $variant->images
            ->sortBy('position')
            ->values()
            ->map(function (VariantImage $image) use ($variant) {
                $full = $image->image_url;
                $thumb = $image->thumbnail_url ?? $full;

                return [
                    'original' => $full,
                    'xl' => $full,
                    'lg' => $full,
                    'md' => $full,
                    'sm' => $full,
                    'xs' => $thumb,
                    'alt' => $image->formatted_alt_text ?? $variant->display_name ?? $this->product->name,
                    'title' => $variant->display_name ?? $variant->name ?? $this->product->name,
                    'generated' => false,
                ];
            })
            ->toArray();

        return $this->variantImageCache[$variantId] = $images;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product-image-gallery');
    }
}
