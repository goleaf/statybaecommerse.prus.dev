@props(['product', 'containerClass' => null])

@php
    $placeholderImageUrl = asset('images/placeholder-product.jpg');
@endphp

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
        <img src="{{ $placeholderImageUrl }}"
             alt="{{ $product->trans('name') ?? $product->name }}"
             loading="lazy"
             width="300"
             height="300"
             {{ $attributes->merge(['class' => 'size-full max-w-none object-cover object-center group-hover:opacity-75']) }} />
    @endif
</div>