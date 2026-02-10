@props(['product', 'containerClass' => null])

<div @class([
    'aspect-1 ring-1 ring-gray-100 overflow-hidden',
    $containerClass,
])>
    @if ($product->hasImages())
        @php $imageAttrs = $product->getResponsiveImageAttributes('sm'); @endphp
        <img src="{{ $imageAttrs['src'] }}"
             srcset="{{ $imageAttrs['srcset'] }}"
             sizes="{{ $imageAttrs['sizes'] }}"
             alt="{{ $imageAttrs['alt'] }}"
             loading="lazy"
             width="300"
             height="300"
             {{ $attributes->merge(['class' => 'size-full max-w-none object-cover object-center group-hover:opacity-75']) }} />
    @else
        <div
             {{ $attributes->merge(['class' => 'size-full max-w-none bg-gray-200 flex items-center justify-center']) }}>
            <span
                  class="text-sm text-gray-500 font-medium">{{ strtoupper(substr($product->trans('name'), 0, 3)) }}</span>
        </div>
    @endif
</div>
