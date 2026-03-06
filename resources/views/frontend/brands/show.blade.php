@extends('frontend.layouts.app')

@section('title', $brand->getTranslatedSeoTitle())
@section('description', strip_tags((string) $brand->getTranslatedDescription()))

@section('content')
@php
    $brandName = $brand->trans('name') ?? (is_string($brand->name) ? $brand->name : (string) data_get($brand->name, app()->getLocale(), ''));
    $fallbackSections = collect($categoryProductSections ?? []);
    $displayedProductCount = $products->count() > 0
        ? $products->total()
        : $fallbackSections->sum(static fn (array $section): int => $section['products']->count());
@endphp
<div class="min-h-screen bg-sage brand-products-page">
    {{-- Dark Banner Section --}}
    <div class="bg-dark text-sage">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-white" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home') }}" class="text-white hover:text-white transition-colors">
                            {{ __('nav.home') }}
                        </a>
                    </li>
                    <li class="text-white">/</li>
                    <li>
                        <a href="{{ route('frontend.brands.index') }}" class="text-white hover:text-white transition-colors">
                            {{ __('messages.brands') }}
                        </a>
                    </li>
                    <li class="text-white">/</li>
                    <li class="text-white">{{ $brandName }}</li>
                </ol>
            </nav>

            <div class="space-y-6">
                {{-- Brand Info Section --}}
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl space-y-4">
                        @if($brand->getFirstMediaUrl('logo'))
                            <div class="mb-4">
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                    alt="{{ $brandName }}"
                                    class="h-16 w-auto object-contain"
                                />
                            </div>
                        @endif

                        <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                            {{ __('messages.brand_spotlight') }}
                        </span>
                        
                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                            {{ $brandName }}
                        </h1>
                        
                        @if($brand->description)
                            <p class="text-base text-white/90 sm:text-lg">
                                {{ $brand->description }}
                            </p>
                        @endif

                        @if($brand->website)
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                                </svg>
                                <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="font-medium text-white hover:text-sage transition-colors">
                                {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}
                            </a>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-end sm:gap-4">
                        <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm font-semibold text-white shadow-sm">
                            {{ number_format($displayedProductCount) }} {{ __('messages.products') }}
                        </div>
                        <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm text-white/80 shadow-sm">
                            {{ number_format($relatedCategories->count()) }} {{ __('messages.subcategories') }}
                        </div>
                    </div>
                </div>

                {{-- Categories Section in Banner (Compact) --}}
                @if($relatedCategories->count() > 0)
                    <div class="border-t border-sage/30 pt-3 mt-6">
                        <div class="mb-2">
                            <h2 class="text-base font-bold text-white mb-0.5">{{ __('messages.categories_brand_powers') }}</h2>
                        </div>
                        
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($relatedCategories as $category)
                                @php
                                    $categoryName = $category->trans('name') ?? (is_string($category->name) ? $category->name : (string) data_get($category->name, app()->getLocale(), ''));
                                @endphp
                                <a href="{{ route('frontend.categories.show', $category) }}" class="group rounded-md border border-sage/30 bg-sage/10 p-2 text-white transition hover:border-sage hover:bg-sage/20">
                                    <h3 class="text-xs font-semibold text-white group-hover:text-sage transition-colors leading-tight">
                                        {{ $categoryName }}
                                    </h3>
                                    <p class="text-[10px] text-white/60 mt-0.5 leading-tight">
                                        {{ number_format($category->published_products_count ?? 0) }} {{ __('messages.products') }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                    </div>
                </div>
            </div>

    {{-- Products Section --}}
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Filters and Sort Section (Dark Background) --}}
        <div class="mb-8 rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                {{-- Quick Filters --}}
                <form method="get" class="flex flex-wrap items-center gap-3 text-sm font-medium">
                    <span class="text-white/80 font-semibold">{{ __('messages.quick_filters') }}</span>
                        @foreach ($availableFilters as $key => $label)
                        <label class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-dark transition hover:border-sage hover:bg-sage/20 cursor-pointer">
                            <input type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-dark focus:ring-dark" />
                            <span class="text-dark font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                    <label class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-dark transition hover:border-sage hover:bg-sage/20 cursor-pointer">
                        <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-dark focus:ring-dark" />
                        <span class="text-dark font-medium">{{ __('messages.all_products') }}</span>
                    </label>
                    <button type="submit" class="rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm hover:bg-sage/90 transition-colors">
                        {{ __('messages.apply') }}
                    </button>
                    </form>

                {{-- Sort Form --}}
                    <form method="get" class="flex items-center gap-3 text-sm">
                        @php
                            $flattenQueryParams = function (array $params, string $prefix = '') use (&$flattenQueryParams): array {
                                $flattened = [];

                                foreach ($params as $queryKey => $queryValue) {
                                    $inputName = $prefix === '' ? (string) $queryKey : "{$prefix}[{$queryKey}]";

                                    if (is_array($queryValue)) {
                                        $flattened = array_merge($flattened, $flattenQueryParams($queryValue, $inputName));
                                        continue;
                                    }

                                    $flattened[] = [
                                        'name' => $inputName,
                                        'value' => is_scalar($queryValue) ? (string) $queryValue : '',
                                    ];
                                }

                                return $flattened;
                            };

                            $sortPreservedInputs = $flattenQueryParams(request()->except('sort'));
                        @endphp
                        @foreach ($sortPreservedInputs as $input)
                            <input type="hidden" name="{{ $input['name'] }}" value="{{ $input['value'] }}" />
                        @endforeach
                    <label for="sort" class="text-white/80 font-semibold">{{ __('messages.sort_by') }}</label>
                    <select id="sort" name="sort" class="rounded-none border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-medium text-white focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage">
                            @foreach ($availableSorts as $key => $label)
                            <option value="{{ $key }}" @selected($activeSort === $key) class="bg-dark text-white">{{ $label }}</option>
                            @endforeach
                        </select>
                    <button type="submit" class="rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm hover:bg-sage/90 transition-colors">
                        {{ __('messages.update') }}
                    </button>
                    </form>
                </div>
        </div>

        {{-- Products Grid --}}
        @if($products->count() > 0)
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-8">
                @foreach ($products as $product)
                    @php
                        $productData = $product->toArray();
                        $productId = $productData['id'];
                        $inStock = $productData['stock_quantity'] > 0;
                    @endphp
                    <div class="relative product-card-wrapper" data-product-id="{{ $productId }}" data-in-stock="{{ $inStock ? '1' : '0' }}">
                        @include('livewire.home.partials.product-card', [
                            'product' => $product,
                            'preset' => 'featured',
                            'attributes' => new \Illuminate\View\ComponentAttributeBag(),
                        ])
                    </div>
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-12 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    {{ $products->onEachSide(1)->links('pagination::tailwind') }}
                </div>
            @endif
        @elseif($fallbackSections->isNotEmpty())
            <div class="space-y-10 mb-8">
                @foreach ($fallbackSections as $section)
                    @php
                        /** @var \App\Models\Category $sectionCategory */
                        $sectionCategory = $section['category'];
                        /** @var \Illuminate\Support\Collection<int, \App\Models\Product> $sectionProducts */
                        $sectionProducts = $section['products'];
                        $sectionCategoryName = $sectionCategory->trans('name') ?? (is_string($sectionCategory->name) ? $sectionCategory->name : (string) data_get($sectionCategory->name, app()->getLocale(), ''));
                    @endphp
                    <section class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-xl font-bold text-dark">{{ $sectionCategoryName }}</h3>
                            <span class="rounded-full border border-sage/40 bg-sage px-3 py-1 text-xs font-semibold uppercase tracking-wide text-dark">
                                {{ number_format($sectionProducts->count()) }} {{ __('messages.products') }}
                            </span>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($sectionProducts as $product)
                                @php
                                    $sectionProductData = $product->toArray();
                                    $sectionProductId = $sectionProductData['id'];
                                    $sectionInStock = $sectionProductData['stock_quantity'] > 0;
                                @endphp
                                <div class="relative product-card-wrapper" data-product-id="{{ $sectionProductId }}" data-in-stock="{{ $sectionInStock ? '1' : '0' }}">
                                    @include('livewire.home.partials.product-card', [
                                        'product' => $product,
                                        'preset' => 'featured',
                                        'attributes' => new \Illuminate\View\ComponentAttributeBag(),
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-sage/30 bg-dark/50 p-12 text-center mb-8">
                <p class="text-white/60">{{ __('messages.no_products_brand') }}</p>
            </div>
        @endif
    </div>
</div>

<script>
(function() {
    const cartAddUrl = '{{ route("frontend.cart.add") }}';
    const addToCartText = '{{ __("frontend.home.products.actions.add_to_cart") }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    function replaceAddToCartButtons() {
        document.querySelectorAll('.product-card-wrapper').forEach(function(wrapper) {
            const productId = wrapper.getAttribute('data-product-id');
            const inStock = wrapper.getAttribute('data-in-stock') === '1';
            const wireButton = wrapper.querySelector('button[wire\\:click]');
            
            if (!wireButton || !productId) return;
            
            // Create form to replace the button
            const form = document.createElement('form');
            form.action = cartAddUrl;
            form.method = 'POST';
            form.style.display = 'inline-block';
            form.className = 'product-card-add-to-cart-form';
            
            const buttonHtml = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" 
                        ${!inStock ? 'disabled' : ''}
                        class="flex items-center cursor-pointer text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:opacity-90"
                        style="background-color: #262523 !important;">
                    <span>${addToCartText}</span>
                </button>
            `;
            
            form.innerHTML = buttonHtml;
            
            // Replace the wire button with the form
            wireButton.replaceWith(form);
        });
    }
    
    // Wait for DOM to be ready and replace buttons
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', replaceAddToCartButtons);
    } else {
        replaceAddToCartButtons();
    }
    
    // Also try after a short delay to ensure all content is loaded (e.g., if Livewire loads content asynchronously)
    setTimeout(replaceAddToCartButtons, 100);
    setTimeout(replaceAddToCartButtons, 500);
})();
</script>
@endsection
