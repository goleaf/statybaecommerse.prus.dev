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
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('platform_statistics') }}</h2>
            </div>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <div class="text-center">
                    <x-shared.badge variant="primary" size="lg" class="text-2xl font-bold px-4 py-2">
                        {{ $this->featuredProducts->count() }}+
                    </x-shared.badge>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.products') }}</div>
                </div>
                <div class="text-center">
                    <x-shared.badge variant="success" size="lg" class="text-2xl font-bold px-4 py-2">
                        {{ $this->topCategories->count() }}+
                    </x-shared.badge>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.categories') }}</div>
                </div>
                <div class="text-center">
                    <x-shared.badge variant="warning" size="lg" class="text-2xl font-bold px-4 py-2">
                        {{ $this->topBrands->count() }}+
                    </x-shared.badge>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.brands') }}</div>
                </div>
                <div class="text-center">
                    <x-shared.badge variant="info" size="lg" class="text-2xl font-bold px-4 py-2">
                        {{ $this->featuredCollections->count() }}+
                    </x-shared.badge>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('admin.models.collections') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured Collections --}}
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-shared.section 
                title="{{ __('home_shop_by_collection') }}"
                description="{{ __('home_collections_desc') }}"
                icon="heroicon-o-squares-2x2"
                titleSize="text-3xl"
                centered="true"
            />
                
                <div class="mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
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
                                <x-shared.badge variant="secondary" class="mb-3">
                                    {{ __('collection') }}
                                </x-shared.badge>
                                
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                    {{ $collection->name }}
                                </h3>
                                @if($collection->description)
                                    <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">
                                        {{ $collection->description }}
                                    </p>
                                @endif
                                
                                <div class="mt-4">
                                    <x-shared.button 
                                        href="{{ route('collections.show', ['slug' => $collection->slug, 'locale' => app()->getLocale()]) }}"
                                        variant="primary"
                                        size="sm"
                                        icon="heroicon-o-arrow-right"
                                        iconPosition="right"
                                        class="group-hover:shadow-md transition-shadow duration-200"
                                    >
                                        {{ __('shared.view') }}
                                    </x-shared.button>
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
                    <x-shared.product-card 
                        :product="$product" 
                        :showQuickAdd="true" 
                        :showWishlist="true" 
                        :showCompare="true" 
                    />
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <x-shared.button 
                    href="{{ route('products.index') }}"
                    variant="primary"
                    size="lg"
                    icon="heroicon-o-arrow-right"
                    iconPosition="right"
                    class="transform hover:scale-105 transition-all duration-200 shadow-lg"
                >
                    {{ __('View All Products') }}
                </x-shared.button>
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
    <section class="bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 dark:from-blue-800 dark:via-purple-800 dark:to-blue-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <h2 class="text-white text-3xl font-bold">{{ __('Stay updated with our latest products') }}</h2>
                    </div>
                    <p class="text-blue-100 text-lg">{{ __('Get notified about new arrivals, special offers, and exclusive deals') }}</p>
                </div>
                
                <form class="mx-auto mt-8 max-w-md" wire:submit="subscribeNewsletter">
                    <div class="space-y-4">
                        <label for="newsletter-email" class="block text-sm font-medium text-white">{{ __('newsletter_subscription') }}</label>
                        
                        <div class="flex gap-x-4">
                            <input 
                                wire:model="newsletterEmail"
                                type="email" 
                                id="newsletter-email"
                                placeholder="{{ __('Enter your email') }}"
                                class="min-w-0 flex-auto rounded-lg bg-white/10 border border-white/20 px-4 py-3 text-white placeholder:text-blue-200 focus:border-white/40 focus:ring-2 focus:ring-white/25 focus:outline-none backdrop-blur-sm"
                            />
                            <button 
                                type="submit"
                                class="flex-none inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-medium text-gray-900 shadow-lg hover:bg-gray-100 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-white/25 transition-all duration-200"
                            >
                                {{ __('Subscribe') }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
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