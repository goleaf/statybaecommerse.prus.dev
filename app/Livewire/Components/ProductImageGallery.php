<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Attributes\Computed;
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

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product, string $imageSize = 'lg'): void
    {
        $this->product = $product;
        $this->imageSize = $imageSize;
    }

    /**
     * Handle images functionality with proper error handling.
     */
    #[Computed]
    public function images(): array
    {
        return $this->product->getGalleryImages();
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
        $this->currentImageIndex = $this->currentImageIndex < count($this->images) - 1 ? $this->currentImageIndex + 1 : 0;
    }

    /**
     * Handle previousImage functionality with proper error handling.
     */
    public function previousImage(): void
    {
        $this->currentImageIndex = $this->currentImageIndex > 0 ? $this->currentImageIndex - 1 : count($this->images) - 1;
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

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product-image-gallery');
    }
}
