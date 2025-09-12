<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProductImageGallery extends Component
{
    public Product $product;

    public int $currentImageIndex = 0;

    public bool $showLightbox = false;

    public string $imageSize = 'lg';

    public function mount(Product $product, string $imageSize = 'lg'): void
    {
        $this->product = $product;
        $this->imageSize = $imageSize;
    }

    #[Computed]
    public function images(): array
    {
        return $this->product->getGalleryImages();
    }

    #[Computed]
    public function hasImages(): bool
    {
        return $this->product->hasImages();
    }

    #[Computed]
    public function currentImage(): ?array
    {
        return $this->images[$this->currentImageIndex] ?? null;
    }

    public function nextImage(): void
    {
        $this->currentImageIndex = $this->currentImageIndex < count($this->images) - 1
            ? $this->currentImageIndex + 1
            : 0;
    }

    public function previousImage(): void
    {
        $this->currentImageIndex = $this->currentImageIndex > 0
            ? $this->currentImageIndex - 1
            : count($this->images) - 1;
    }

    public function selectImage(int $index): void
    {
        if (isset($this->images[$index])) {
            $this->currentImageIndex = $index;
        }
    }

    public function toggleLightbox(): void
    {
        $this->showLightbox = ! $this->showLightbox;
    }

    public function render()
    {
        return view('livewire.components.product-image-gallery');
    }
}
