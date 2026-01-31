<?php

declare(strict_types=1);

namespace App\Livewire\Components\Product;

use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Images
 *
 * Livewire component for Images with reactive frontend functionality, real-time updates, and user interaction handling.
 */
#[Lazy(isolate: false)]
class Images extends Component
{
    public ?string $thumbnail = null;

    /**
     * @var array<int, mixed>
     */
    public array $images = [];

    public int $active = 0;

    public function placeholder(): string
    {
        return <<<'BLADE'
        <div class="space-y-6">
            <div class="h-[27.35rem] rounded-md bg-gray-100 animate-pulse" />
            <div class="grid grid-cols-3 gap-6">
                <div class="rounded-md bg-gray-100 h-32 w-full animate-pulse" />
                <div class="rounded-md bg-gray-100 h-32 w-full animate-pulse" />
                <div class="rounded-md bg-gray-100 h-32 w-full animate-pulse" />
            </div>
        </div>
        BLADE;
    }

    #[On('variant.selected')]
    public function variantSelected(?int $variantId = null): void
    {
        if ($variantId) {
            $variant = ProductVariant::with('media', 'product.media')->select('product_id', 'id')->find($variantId);

            if (! $variant) {
                return;
            }

            $this->thumbnail = $variant->getMedia(config('media.storage.thumbnail_collection'))->isNotEmpty()
                ? ($variant->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: $variant->getFirstMediaUrl(config('media.storage.collection_name'), 'large'))
                : ($variant->product->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: $variant->product->getFirstMediaUrl(config('media.storage.collection_name'), 'large'));

            $this->images = $variant->getMedia(config('media.storage.collection_name'))->isNotEmpty()
                ? $variant
                    ->getMedia(config('media.storage.collection_name'))
                    ->map(function ($media) use ($variant) {
                        return [
                            'src'    => $media->getUrl('large') ?: $media->getUrl(),
                            'srcset' => trim(($media->getUrl('medium') ?: '') . ' 500w, ' . ($media->getUrl('large') ?: '')),
                            'alt'    => $variant->product?->trans('name') ?? ($variant->product?->name ?? 'Product image'),
                        ];
                    })
                    ->toArray()
                : $variant->product
                    ->getMedia(config('media.storage.collection_name'))
                    ->map(function ($media) use ($variant) {
                        return [
                            'src'    => $media->getUrl('large') ?: $media->getUrl(),
                            'srcset' => trim(($media->getUrl('medium') ?: '') . ' 500w, ' . ($media->getUrl('large') ?: '')),
                            'alt'    => $variant->product?->trans('name') ?? ($variant->product?->name ?? 'Product image'),
                        ];
                    })
                    ->toArray();
        }
    }

    /**
     * Handle setActive functionality with proper error handling.
     */
    public function setActive(int $index): void
    {
        $this->active = max(0, min($index, count($this->images) - 1));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.product.images');
    }
}
