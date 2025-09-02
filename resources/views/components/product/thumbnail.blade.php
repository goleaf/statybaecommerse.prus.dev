@props(['product', 'containerClass' => null])

<div @class([
    'aspect-1 ring-1 ring-gray-100 overflow-hidden',
    $containerClass,
])>
    @php
        $collection = config('shopper.media.storage.collection_name');
        $thumbCol = config('shopper.media.storage.thumbnail_collection');
        $srcSmall = $product->getFirstMediaUrl($collection, 'small');
        $srcMedium = $product->getFirstMediaUrl($collection, 'medium');
        $srcLarge = $product->getFirstMediaUrl($collection, 'large');
        $srcThumb = $product->getFirstMediaUrl($thumbCol);
        $src = $srcThumb ?: ($srcSmall ?: ($srcMedium ?: ($srcLarge ?: $product->getFirstMediaUrl($collection))));
        $srcset = [];
        if ($srcSmall) {
            $srcset[] = $srcSmall . ' 300w';
        }
        if ($srcMedium) {
            $srcset[] = $srcMedium . ' 500w';
        }
        if ($srcLarge) {
            $srcset[] = $srcLarge . ' 800w';
        }
        $sizes = '(max-width: 640px) 45vw, (max-width: 1024px) 22vw, 200px';
    @endphp
    <img
         src="{{ $src }}"
         @if (!empty($srcset)) srcset="{{ implode(', ', $srcset) }}" sizes="{{ $sizes }}" @endif
         alt="{{ $product->trans('name') }} thumbnail"
         loading="lazy"
         width="300"
         height="300"
         {{ $attributes->merge(['class' => 'size-full max-w-none object-cover object-center group-hover:opacity-75']) }} />
</div>
