@section('meta')
    @php
        $websiteJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => url('/'),
            'name' => config('app.name'),
            'description' => __('meta_description_home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('search.index', ['locale' => app()->getLocale()]) . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <x-meta
        :title="__('nav_home') . ' - ' . config('app.name')"
        :description="__('meta_description_home')"
        :og-image="Vite::asset('resources/images/hero.png')"
        canonical="{{ url()->current() }}"
        :jsonld="json_encode($websiteJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 dark:from-blue-800 dark:via-purple-800 dark:to-blue-900">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                    {{ __('home_new_arrivals') }}
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-100 sm:text-xl">
                    {{ __('home_new_arrivals_desc') }}
                </p>
                
                {{-- Enhanced Search Bar --}}
                <div class="mx-auto mt-10 max-w-xl">
                    <form wire:submit="search" class="flex gap-x-4">
                        <input 
                            wire:model="searchQuery"
                            type="search" 
                            placeholder="{{ __('search_placeholder') }}"
                            class="min-w-0 flex-auto rounded-xl border-0 bg-white/10 px-6 py-4 text-white placeholder:text-gray-300 focus:ring-2 focus:ring-white/25 backdrop-blur-sm"
                        />
                        <button 
                            type="submit"
                            class="flex-none rounded-xl bg-white px-6 py-4 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-all duration-200"
                        >
                            {{ __('search') }}
                        </button>
                    </form>
                </div>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('products.index') }}" 
                       class="rounded-xl bg-white px-8 py-4 text-sm font-semibold text-gray-900 shadow-lg hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-all duration-200 transform hover:scale-105">
                        {{ __('home_shop_now') }}
                    </a>
                    <a href="{{ route('categories.index') }}" 
                       class="text-sm font-semibold leading-6 text-white hover:text-gray-200 transition-colors duration-200">
                        {{ __('categories_browse') }} <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Floating Elements --}}
        <div class="absolute top-1/4 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-10 w-32 h-32 bg-purple-400/20 rounded-full blur-xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/3 right-1/4 w-16 h-16 bg-blue-400/20 rounded-full blur-xl animate-pulse delay-500"></div>
    </section>

    {{-- Quick Stats --}}
    <section class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->featuredProducts->count() }}+</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.products') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->topCategories->count() }}+</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.categories') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->topBrands->count() }}+</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.brands') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->featuredCollections->count() }}+</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.collections') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Collections --}}
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ __('home_shop_by_collection') }}
                </h2>
                <p class="mx-auto mt-4 max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                    {{ __('home_collections_desc') }}
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->featuredCollections as $collection)
                    <div class="group relative overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 transition-all duration-300 hover:shadow-xl hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700 dark:hover:ring-gray-600">
                        @if($collection->getFirstMediaUrl('images'))
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                <img 
                                    src="{{ $collection->getFirstMediaUrl('images') }}" 
                                    alt="{{ $collection->name }}"
                                    class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                />
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                {{ $collection->name }}
                            </h3>
                            @if($collection->description)
                                <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">
                                    {{ $collection->description }}
                                </p>
                            @endif
                            
                            <div class="mt-4">
                                <a href="{{ route('collections.show', ['slug' => $collection->slug, 'locale' => app()->getLocale()]) }}" 
                                   class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                                    {{ __('btn_view') }}
                                    <svg class="ml-1 h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured Products --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-900 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ __('home_trending_products') }}
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    {{ __('Discover our most popular products chosen by customers') }}
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($this->featuredProducts as $product)
                    <div class="group relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700">
                        <div class="aspect-w-1 aspect-h-1 overflow-hidden">
                            @if($product->getFirstMediaUrl('gallery'))
                                <img 
                                    src="{{ $product->getFirstMediaUrl('gallery') }}" 
                                    alt="{{ $product->name }}"
                                    class="h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                />
                            @else
                                <div class="flex h-64 items-center justify-center bg-gray-200 dark:bg-gray-700">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="p-4">
                            @if($product->brand)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand->name }}</p>
                            @endif
                            
                            <h3 class="mt-1 text-lg font-medium text-gray-900 dark:text-white line-clamp-2">
                                <a href="{{ route('products.show', ['slug' => $product->slug, 'locale' => app()->getLocale()]) }}" 
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            
                            @if($product->summary)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                    {{ $product->summary }}
                                </p>
                            @endif
                            
                            <div class="mt-4 flex items-center justify-between">
                                @if($product->prices->isNotEmpty())
                                    <div class="flex items-center space-x-2">
                                        @php $price = $product->prices->first(); @endphp
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $price->currency->symbol }}{{ number_format($price->amount, 2) }}
                                        </span>
                                        @if($product->sale_price && $product->sale_price < $price->amount)
                                            <span class="text-sm text-gray-500 line-through">
                                                {{ $price->currency->symbol }}{{ number_format($product->sale_price, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
                                <button 
                                    wire:click="addToCart({{ $product->id }})"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105"
                                >
                                    {{ __('cart_add_to_cart') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center rounded-xl bg-blue-600 px-8 py-4 text-base font-medium text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    {{ __('View All Products') }}
                    <svg class="ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- New Arrivals --}}
    @if($this->newArrivals->isNotEmpty())
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ __('New Arrivals') }}
                    </h2>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" 
                       class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ __('View all') }} →
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($this->newArrivals->take(8) as $product)
                        <x-product-card :product="$product" :show-quick-add="true" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Top Categories --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-900 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ __('categories_browse') }}
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    {{ __('Explore our comprehensive range of categories') }}
                </p>
            </div>

            <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($this->topCategories as $category)
                    <a href="{{ route('categories.show', ['slug' => $category->slug, 'locale' => app()->getLocale()]) }}" 
                       class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700">
                        @if($category->getFirstMediaUrl('images'))
                            <div class="absolute inset-0 opacity-10 group-hover:opacity-20 transition-opacity duration-300">
                                <img 
                                    src="{{ $category->getFirstMediaUrl('images') }}" 
                                    alt="{{ $category->name }}"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        @endif
                        
                        <div class="relative text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                {{ $category->name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->products_count }} {{ __('admin.models.products') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Top Brands --}}
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ __('brands_browse') }}
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    {{ __('Shop from our trusted brand partners') }}
                </p>
            </div>

            <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($this->topBrands as $brand)
                    <a href="{{ route('brands.show', ['slug' => $brand->slug, 'locale' => app()->getLocale()]) }}" 
                       class="group flex flex-col items-center justify-center rounded-xl bg-white p-6 shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700">
                        @if($brand->getFirstMediaUrl('logo'))
                            <img 
                                src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                alt="{{ $brand->name }}"
                                class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-110"
                                loading="lazy"
                            />
                        @endif
                        
                        <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 text-center">
                            {{ $brand->name }}
                        </h3>
                        
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $brand->products_count }} {{ __('admin.models.products') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Newsletter Signup --}}
    <section class="bg-blue-600 dark:bg-blue-800">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    {{ __('Stay updated with our latest products') }}
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-blue-100">
                    {{ __('Get notified about new arrivals, special offers, and exclusive deals') }}
                </p>
                
                <form class="mx-auto mt-8 max-w-md">
                    <div class="flex gap-x-4">
                        <input 
                            type="email" 
                            placeholder="{{ __('Enter your email') }}"
                            class="min-w-0 flex-auto rounded-xl border-0 bg-white/10 px-6 py-4 text-white placeholder:text-blue-200 focus:ring-2 focus:ring-white/25 backdrop-blur-sm"
                        />
                        <button 
                            type="submit"
                            class="flex-none rounded-xl bg-white px-6 py-4 text-sm font-semibold text-blue-600 shadow-sm hover:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-all duration-200"
                        >
                            {{ __('Subscribe') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    // Enhanced interactivity
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Add to cart animation
        window.addEventListener('cart:added', function(e) {
            // Create floating notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            notification.textContent = `${e.detail.product} added to cart!`;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            
            // Animate out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        });
    });
</script>
@endpush