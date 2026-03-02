@extends('frontend.layouts.app')

@section('title', $category->getTranslatedSeoTitle())
@section('description', strip_tags((string) $category->getTranslatedDescription()))

@section('content')
@php
    $categoryName = $category->trans('name') ?? (is_string($category->name) ? $category->name : (string) data_get($category->name, app()->getLocale(), ''));
    $categoryDescription = $category->trans('description')
        ?? (is_string($category->description) ? $category->description : (string) data_get($category->description, app()->getLocale(), ''));
@endphp
<div class="min-h-screen bg-sage brand-products-page">
    <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <nav class="text-sm text-dark/70" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
            <ol class="flex flex-wrap items-center gap-2">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-dark transition-colors">
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>/</li>
                <li>
                    <a href="{{ route('frontend.categories.index') }}" class="hover:text-dark transition-colors">
                        {{ __('messages.categories') }}
                    </a>
                </li>
                <li>/</li>
                <li class="text-dark">{{ $categoryName }}</li>
            </ol>
        </nav>
    </div>

    <div class="bg-dark text-sage">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.35em] text-dark">
                            {{ __('ui.category_spotlight') }}
                        </span>

                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                            {{ $categoryName }}
                        </h1>

                        @if ($categoryDescription !== '')
                            <p class="text-base text-white/90 sm:text-lg">
                                {{ strip_tags($categoryDescription) }}
                            </p>
                        @endif

                        @if ($category->parent)
                            <p class="text-sm text-white/80">
                                {{ __('ui.part_of_parent', ['parent' => $category->parent->name]) }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-end sm:gap-4">
                        <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm font-semibold text-white shadow-sm">
                            {{ number_format($products->total()) }} {{ __('messages.products') }}
                        </div>
                        <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm text-white/80 shadow-sm">
                            {{ number_format($relatedCategories->count()) }} {{ __('messages.subcategories') }}
                        </div>
                        <div class="rounded-2xl border border-sage/30 bg-sage/10 px-3 py-2 text-sm text-white/80 shadow-sm">
                            {{ number_format($highlightedBrands->count()) }} {{ __('ui.featured_brands') }}
                        </div>
                    </div>
                </div>

                @if($relatedCategories->count() > 0)
                    <div class="border-t border-sage/30 pt-3 mt-6">
                        <div class="mb-2">
                            <h2 class="text-base font-bold text-white mb-0.5">{{ __('ui.explore_related_categories') }}</h2>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($relatedCategories as $related)
                                <a href="{{ route('frontend.categories.show', $related) }}" class="group rounded-md border border-sage/30 bg-sage/10 p-2 text-white transition hover:border-sage hover:bg-sage/20">
                                    <h3 class="text-xs font-semibold text-white group-hover:text-sage transition-colors leading-tight">
                                        {{ $related->name }}
                                    </h3>
                                    @if ($related->description)
                                        <p class="text-[10px] text-white/60 mt-0.5 leading-tight">
                                            {!! str($related->description)->stripTags()->limit(70) !!}
                                        </p>
                                    @else
                                        <p class="text-[10px] text-white/60 mt-0.5 leading-tight">
                                            {{ __('ui.products_available_2') }} {{ number_format($related->published_products_count ?? 0) }}
                                        </p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-3xl border border-sage/30 bg-dark p-6 shadow-lg">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <form method="get" class="flex flex-wrap items-center gap-3 text-sm font-medium">
                    <span class="text-white/80 font-semibold">{{ __('ui.quick_filters') }}</span>
                    @foreach ($availableFilters as $key => $label)
                        <label class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-dark transition hover:border-sage hover:bg-sage/20 cursor-pointer">
                            <input type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-dark focus:ring-dark" />
                            <span class="text-dark font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                    <label class="inline-flex items-center gap-2 rounded-full border border-sage/30 bg-sage/10 px-4 py-2 text-dark transition hover:border-sage hover:bg-sage/20 cursor-pointer">
                        <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-dark focus:ring-dark" />
                        <span class="text-dark font-medium">{{ __('ui.all_products') }}</span>
                    </label>
                    <button type="submit" class="rounded-full border border-sage bg-sage px-4 py-2 text-sm font-semibold text-dark shadow-sm hover:bg-sage/90 transition-colors">
                        {{ __('messages.apply') }}
                    </button>
                </form>

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
                <p class="text-white/60">{{ __('ui.no_products_available_for_this_category_yet') }}</p>
            </div>
        @endif

        <section class="mt-12 grid gap-8 lg:grid-cols-2">
            <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-white">{{ __('ui.explore_related_categories') }}</h2>
                <ul class="mt-4 grid gap-3 text-sm text-white/80 sm:grid-cols-2">
                    @forelse ($relatedCategories as $related)
                        <li class="rounded-2xl border border-sage/30 bg-sage/10 p-4 transition hover:border-sage hover:bg-sage/20">
                            <a href="{{ route('frontend.categories.show', $related) }}" class="font-semibold text-white hover:text-sage">{{ $related->name }}</a>
                            @if ($related->description)
                                <p class="mt-2 text-xs text-white/70">{!! str($related->description)->stripTags()->limit(100) !!}</p>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-sage/30 bg-sage/10 p-6 text-center text-sm text-white/70">{{ __('ui.additional_categories_will_appear_here_as_soon_as_they_are_available') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-3xl border border-sage/30 bg-dark p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-white">{{ __('ui.featured_brands_in_this_category') }}</h2>
                <ul class="mt-4 space-y-3 text-sm text-white/80">
                    @forelse ($highlightedBrands as $brand)
                        <li class="flex items-center justify-between rounded-2xl border border-sage/30 bg-sage/10 px-4 py-3">
                            <a href="{{ route('frontend.brands.show', $brand) }}" class="font-semibold text-white hover:text-sage">{{ $brand->name }}</a>
                            <span class="rounded-full bg-sage px-2 py-0.5 text-xs font-semibold text-dark">{{ number_format($brand->published_products_count ?? $brand->products_count ?? 0) }}</span>
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-sage/30 bg-sage/10 p-6 text-center text-sm text-white/70">{{ __('ui.brand_highlights_will_appear_as_soon_as_products_are_published') }}</li>
                    @endforelse
                </ul>
            </div>
        </section>
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
            wireButton.replaceWith(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', replaceAddToCartButtons);
    } else {
        replaceAddToCartButtons();
    }

    setTimeout(replaceAddToCartButtons, 100);
    setTimeout(replaceAddToCartButtons, 500);
})();
</script>
@endsection
