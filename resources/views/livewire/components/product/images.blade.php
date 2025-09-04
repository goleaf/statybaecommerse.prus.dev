<div>
    <div class="aspect-square bg-white flex items-center justify-center border rounded-md">
        @if (!empty($images[$active]['src']))
            <img src="{{ $images[$active]['src'] }}" alt="{{ $images[$active]['alt'] ?? '' }}"
                 srcset="{{ $images[$active]['srcset'] ?? '' }}"
                 sizes="(max-width: 1024px) 60vw, 800px"
                 width="800" height="800" loading="eager" fetchpriority="high"
                 class="max-h-[480px] object-contain" />
        @elseif ($thumbnail)
            <img src="{{ $thumbnail }}" alt="" width="800" height="800" loading="eager"
                 fetchpriority="high"
                 class="max-h-[480px] object-contain" />
        @endif
    </div>

    @if (count($images) > 1)
        <div class="mt-4 grid grid-cols-4 gap-3">
            @foreach ($images as $idx => $img)
                <button type="button" wire:click="setActive({{ $idx }})"
                        class="border rounded-md {{ $idx === $active ? 'ring-2 ring-primary-500' : '' }}">
                    <img src="{{ $img['src'] }}" alt="{{ $img['alt'] ?? '' }}" width="150" height="150"
                         loading="lazy"
                         class="aspect-square object-cover" />
                </button>
            @endforeach
        </div>
    @endif
</div>

<?php
use App\Models\ProductVariant;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Lazy(isolate: false)] class extends Component {
    /**
     * @var array<int, mixed>
     */
    public array $images = [];

    public string $thumbnail = '';

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

            $this->thumbnail = $variant->getMedia(config('media.storage.thumbnail_collection'))->isNotEmpty() ? ($variant->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: $variant->getFirstMediaUrl(config('media.storage.collection_name'), 'large')) : ($variant->product->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: $variant->product->getFirstMediaUrl(config('media.storage.collection_name'), 'large'));

            $this->images = $variant->getMedia(config('media.storage.collection_name'))->isNotEmpty()
                ? $variant
                    ->getMedia(config('media.storage.collection_name'))
                    ->map(function ($media) use ($variant) {
                        return [
                            'src' => $media->getUrl('large') ?: $media->getUrl(),
                            'srcset' => trim(($media->getUrl('medium') ?: '') . ' 500w, ' . ($media->getUrl('large') ?: '')),
                            'alt' => $variant->product?->trans('name') ?? ($variant->product?->name ?? 'Product image'),
                        ];
                    })
                    ->toArray()
                : $variant->product
                    ->getMedia(config('media.storage.collection_name'))
                    ->map(function ($media) use ($variant) {
                        return [
                            'src' => $media->getUrl('large') ?: $media->getUrl(),
                            'srcset' => trim(($media->getUrl('medium') ?: '') . ' 500w, ' . ($media->getUrl('large') ?: '')),
                            'alt' => $variant->product?->trans('name') ?? ($variant->product?->name ?? 'Product image'),
                        ];
                    })
                    ->toArray();
        }
    }
}; ?>

<div class="space-y-6">
    <div class="aspect-1/2 overflow-hidden">
        <img
             src="{{ $thumbnail }}"
             alt="product thumbnail"
             class="size-full object-cover max-w-none object-center" />
    </div>

    @if (count($images))
        <x-product.gallery :$images />
    @endif
</div>
