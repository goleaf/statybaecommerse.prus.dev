@props(['product', 'containerClass' => null])

<div @class([
    'aspect-1 ring-1 ring-gray-100 overflow-hidden',
    $containerClass,
])>
    @if ($product->getImageUrl('md'))
        <img src="{{ $product->getImageUrl('sm') }}"
             srcset="{{ $product->getImageUrl('xs') }} 150w, {{ $product->getImageUrl('sm') }} 300w, {{ $product->getImageUrl('md') }} 500w, {{ $product->getImageUrl('lg') }} 800w"
             sizes="(max-width: 640px) 45vw, (max-width: 1024px) 22vw, 300px"
             alt="{{ $product->trans('name') }} thumbnail"
             loading="lazy"
             width="300"
             height="300"
             {{ $attributes->merge(['class' => 'size-full max-w-none object-cover object-center group-hover:opacity-75']) }} />
    @elseif ($product->getImageUrl())
        <img src="{{ $product->getImageUrl() }}"
             alt="{{ $product->trans('name') }} thumbnail"
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
