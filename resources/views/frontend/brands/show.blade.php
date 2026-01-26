@extends('frontend.layouts.app')

@section('title', $brand->seo_title ?: $brand->name)
@section('description', $brand->seo_description ?: str($brand->description)->stripTags()->limit(160))

@section('content')
<div class="min-h-screen bg-sage brand-products-page">
    {{-- Dark Banner Section --}}
    <div class="bg-dark text-sage">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="text-xs font-medium uppercase tracking-[0.3em] mb-8 breadcrumb-nav-dark" aria-label="{{ __('messages.shared) }}">
                <ol class="flex items-center gap-3">
                    <li>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 breadcrumb-link-dark transition hover:opacity-80">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
                            </svg>
                            <span class="breadcrumb-link-text-dark">{{ __('messages.frontend) }}</span>
                        </a>
                    </li>
                    <li class="breadcrumb-separator-dark">/</li>
                    <li>
                        <a href="{{ route('frontend.brands.index') }}" class="breadcrumb-link-dark transition hover:opacity-80">
                            <span class="breadcrumb-link-text-dark">{{ __('messages.shared) }}</span>
                        </a>
                    </li>
                    <li class="breadcrumb-separator-dark">/</li>
                    <li>
                        <span class="text-white">{{ $brand->name }}</span>
                    </li>
                    </ol>
                </nav>

            <div class="mt-8 space-y-6">
                {{-- Brand Info Section --}}
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl space-y-4">
                        @if($brand->getFirstMediaUrl('logo'))
                            <div class="mb-4">
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                    alt="{{ $brand->name }}"
                                    class="h-16 w-auto object-contain"
                                />
                            </div>
                        @endif

                        <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                            {{ __('messages.brand_spotlight') }}
                        </span>
                        
                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                            {{ $brand->name }}
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
                            {{ number_format($products->count()) }} {{ __('messages.products') }}
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
                                <a href="{{ route('frontend.categories.show', $category) }}" class="group rounded-md border border-sage/30 bg-sage/10 p-2 text-white transition hover:border-sage hover:bg-sage/20">
                                    <h3 class="text-xs font-semibold text-white group-hover:text-sage transition-colors leading-tight">
                                        {{ $category->name }}
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
                        @foreach (request()->except('sort') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                        @endforeach
                    <label for="sort" class="text-white/80 font-semibold">{{ __('messages.sort_by') }}</label>
                    <select id="sort" name="sort" class="rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-sm font-medium text-white focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage">
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
    const addToCartText = '{{ __("frontend/home.products.actions.add_to_cart") }}';
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
                        class="flex items-center gap-2 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors hover:opacity-90"
                        style="background-color: #262523 !important;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                    </svg>
                    <span>|</span>
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
