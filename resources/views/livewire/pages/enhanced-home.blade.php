@section('meta')
    @php
        $websiteJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('search.index', ['locale' => app()->getLocale()]) . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <x-meta
            :title="__('translations.home_title')"
            :description="__('translations.home_description')"
            :og-image="asset('images/hero-banner.jpg')"
            canonical="{{ url()->current() }}"
            :jsonld="json_encode($websiteJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" />
@endsection

<div class="min-h-screen bg-white">
    <!-- Header with Navigation -->
    <x-layouts.header />

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    {{ __('translations.hero_title') }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
                    {{ __('translations.hero_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" 
                       class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        {{ __('translations.shop_now') }}
                    </a>
                    <a href="{{ route('categories.index') }}" 
                       class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                        {{ __('translations.browse_categories') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    @if($categories->isNotEmpty())
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ __('translations.featured_categories') }}
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    {{ __('translations.categories_description') }}
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($categories as $category)
                <div class="group cursor-pointer" wire:click="navigateToCategory('{{ $category->slug }}')">
                    <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                        @if($category->getFirstMediaUrl('images'))
                            <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}" 
                                 alt="{{ $category->name }}"
                                 class="w-16 h-16 mx-auto mb-4 object-contain">
                        @else
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                            </div>
                        @endif
                        <h3 class="text-sm font-medium text-gray-900 text-center group-hover:text-blue-600 transition-colors">
                            {{ $category->name }}
                        </h3>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Products -->
    @if($products->isNotEmpty())
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ __('translations.featured_products') }}
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    {{ __('translations.products_description') }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
            <div class="text-center mt-12">
                <a href="{{ route('products.index') }}" 
                   class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    {{ __('translations.view_all_products') }}
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Brands Showcase -->
    @if($brands->isNotEmpty())
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ __('translations.trusted_brands') }}
                </h2>
                <p class="text-gray-600">
                    {{ __('translations.brands_description') }}
                </p>
            </div>
            <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-8 gap-8">
                @foreach($brands as $brand)
                <div class="group cursor-pointer" wire:click="navigateToBrand('{{ $brand->slug }}')">
                    <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        @if($brand->getFirstMediaUrl('logo'))
                            <img src="{{ $brand->getFirstMediaUrl('logo', 'thumb') }}" 
                                 alt="{{ $brand->name }}"
                                 class="w-full h-12 object-contain grayscale group-hover:grayscale-0 transition-all">
                        @else
                            <div class="text-center text-sm font-medium text-gray-600">
                                {{ $brand->name }}
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Features Section -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.free_shipping') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ __('translations.free_shipping_desc') }}
                    </p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.quality_guarantee') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ __('translations.quality_guarantee_desc') }}
                    </p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('translations.expert_support') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ __('translations.expert_support_desc') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Signup -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    {{ __('translations.newsletter_title') }}
                </h2>
                <p class="text-blue-100 mb-8 max-w-2xl mx-auto">
                    {{ __('translations.newsletter_description') }}
                </p>
                <form wire:submit="subscribeNewsletter" class="max-w-md mx-auto flex gap-4">
                    <input type="email" 
                           wire:model="email" 
                           placeholder="{{ __('translations.email_placeholder') }}"
                           class="flex-1 px-4 py-3 rounded-lg border-0 text-gray-900">
                    <button type="submit" 
                            class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        {{ __('translations.subscribe') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <x-layouts.footer />

    <!-- Shopping Cart Sidebar -->
    <livewire:components.shopping-cart />
    
    <!-- Quick View Modal -->
    <div x-data="{ quickViewOpen: false }" 
         x-show="quickViewOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         @quick-view.window="quickViewOpen = true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="quickViewOpen = false"></div>
            <div class="relative bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div id="quick-view-content" class="p-6">
                    <!-- Quick view content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-data="{ 
        notifications: [],
        addNotification(notification) {
            this.notifications.push({
                id: Date.now(),
                ...notification
            });
            setTimeout(() => {
                this.removeNotification(this.notifications[0]?.id);
            }, 5000);
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }" 
    @notify.window="addNotification($event.detail)"
    class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg x-show="notification.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg x-show="notification.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    // Enhanced frontend functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Image lazy loading optimization
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    });
</script>
@endpush
