@extends('components.layouts.templates.app', ['title' => $brand->trans('name') ?? $brand->name])

@section('meta')
    <x-meta
            :title="$brand->trans('name') ?? $brand->name"
            :description="Str::limit(strip_tags($brand->trans('description') ?? ($brand->description ?? '')), 150)"
            canonical="{{ url()->current() }}" />
@endsection

@section('content')
    <x-container class="py-8">
        <a href="#results" class="sr-only focus:not-sr-only focus:underline">{{ __('skip_to_results') }}</a>
        @if (session('status'))
            <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
        @endif
        @if ($errors->any())
            <x-alert type="error" class="mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
        <x-breadcrumbs>
            <x-slot name="links">
                <x-link :href="route('brand.index', ['locale' => app()->getLocale()])">{{ __('nav_brands') }}</x-link>
            </x-slot>
        </x-breadcrumbs>

        <!-- Brand Banner -->
        @if ($brand->getBannerUrl('lg'))
            <div class="w-full h-48 md:h-64 lg:h-80 mb-8 rounded-lg overflow-hidden bg-gray-50">
                <img src="{{ $brand->getBannerUrl('md') }}"
                     srcset="{{ $brand->getBannerUrl('sm') }} 800w, {{ $brand->getBannerUrl('md') }} 1200w, {{ $brand->getBannerUrl('lg') }} 1920w"
                     sizes="(max-width: 768px) 800px, (max-width: 1024px) 1200px, 1920px"
                     alt="{{ $brand->trans('name') ?? $brand->name }} banner"
                     class="w-full h-full object-cover" />
            </div>
        @endif

        <div class="flex items-center gap-4 mb-6">
            @if ($brand->getLogoUrl('md'))
                <img src="{{ $brand->getLogoUrl('md') }}" 
                     srcset="{{ $brand->getLogoUrl('sm') }} 128w, {{ $brand->getLogoUrl('md') }} 200w, {{ $brand->getLogoUrl('lg') }} 400w"
                     sizes="200px"
                     alt="{{ $brand->trans('name') ?? $brand->name }} logo"
                     width="200" height="200"
                     class="h-16 w-16 object-contain" />
            @elseif ($brand->getLogoUrl())
                <img src="{{ $brand->getLogoUrl() }}" alt="{{ $brand->trans('name') ?? $brand->name }}"
                     class="h-12 w-12 object-contain" />
            @endif
            <h1 class="text-2xl font-semibold">{{ $brand->trans('name') ?? $brand->name }}</h1>
        </div>

        <p class="text-sm text-gray-600 mb-2" aria-live="polite">
            {{ trans_choice(':count result found|:count results found', $products->total() ?? $products->count(), ['count' => $products->total() ?? $products->count()]) }}
        </p>
        <div id="results" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($products as $product)
                <x-product.card :product="$product" />
            @empty
                <p class="text-gray-500">{{ __('No products for this brand yet.') }}</p>
            @endforelse
        </div>

        <nav class="mt-6" aria-label="{{ __('Pagination') }}">{{ $products->links() }}</nav>
    </x-container>
@endsection

@section('meta')
    @php($ogImage = $brand->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'large') ?: $brand->getFirstMediaUrl(config('shopper.media.storage.collection_name')))
    <x-meta
            :title="$brand->trans('seo_title') ?? ($brand->trans('name') ?? $brand->name)"
            :description="Str::limit(strip_tags($brand->trans('seo_description') ?? ($brand->description ?? '')), 150)"
            :og-image="$ogImage"
            :preload-image="(string) ($products
                ->first()
                ?->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'medium') ?? '')"
            :prev="$products instanceof \Illuminate\Contracts\Pagination\Paginator ||
            $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? $products->previousPageUrl()
                : null"
            :next="$products instanceof \Illuminate\Contracts\Pagination\Paginator ||
            $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? $products->nextPageUrl()
                : null"
            canonical="{{ url()->current() }}" />
@endsection

<x-dynamic-component :component="$layout">
    <div class="container mx-auto px-4 py-8">
        <x-breadcrumbs :items="[
            ['label' => __('Brands'), 'url' => route('brand.index', ['locale' => app()->getLocale()])],
            ['label' => $brand->trans('name') ?? $brand->name],
        ]" />
        <h1 class="text-2xl font-semibold mb-6">{{ $brand->trans('name') ?? $brand->name }}</h1>
        @php($brandThumb = $brand->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($brand->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: $brand->getFirstMediaUrl(config('shopper.media.storage.collection_name'))))
        @if ($brandThumb)
            <div class="mb-6">
                <img loading="lazy" src="{{ $brandThumb }}" alt="{{ $brand->trans('name') ?? $brand->name }}"
                     width="160" height="80"
                     class="h-20 object-contain" />
            </div>
        @endif

        @if (isset($products) && $products->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <a href="{{ route('product.show', ['locale' => app()->getLocale(), 'slug' => $product->trans('slug') ?? $product->slug]) }}"
                       class="block border rounded-lg p-4 hover:shadow-sm">
                        <x-product.thumbnail :product="$product" containerClass="mb-3" />
                        <div class="text-base font-medium">{{ $product->trans('name') ?? $product->name }}</div>
                        <x-product.price :product="$product" class="mt-1" />
                    </a>
                @endforeach
            </div>
            <nav class="mt-6" aria-label="{{ __('Pagination') }}">{{ $products->links() }}</nav>
        @else
            <div class="text-slate-500">{{ __('No products for this brand yet.') }}</div>
        @endif
    </div>
</x-dynamic-component>

@push('scripts')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Brand",
      "name": "{{ addslashes($brand->trans('name') ?? $brand->name) }}",
      @php($logo = $brand->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: $brand->getFirstMediaUrl(config('shopper.media.storage.collection_name')))
      @if ($logo)
      "logo": "{{ $logo }}",
      @endif
      @if ($brand->website)
      "url": "{{ $brand->website }}"
      @endif
    }
    </script>
    @php
        $elements = [];
        $position = 1;
        foreach ($products as $p) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => route('product.show', [
                    'locale' => app()->getLocale(),
                    'slug' => $p->trans('slug') ?? $p->slug,
                ]),
                'name' => $p->trans('name') ?? $p->name,
            ];
        }
    @endphp
    @if (!empty($elements))
        <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $elements], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush

@php($layout = 'layouts.templates.app')

<x-dynamic-component :component="$layout">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ $brand->trans('name') ?? $brand->name }}</h1>
            @if ($brand->website)
                <a href="{{ $brand->website }}" target="_blank"
                   class="text-blue-600 hover:underline text-sm">{{ __('Website') }}</a>
            @endif
        </div>

        @if ($brand->trans('description') ?? $brand->description)
            <div class="prose max-w-none mb-8">{!! nl2br(e($brand->trans('description') ?? $brand->description)) !!}</div>
        @endif

        <h2 class="text-xl font-semibold mb-4">{{ __('Products') }}</h2>
        <livewire:components.brand.products :brandId="$brand->id" />
    </div>
</x-dynamic-component>
