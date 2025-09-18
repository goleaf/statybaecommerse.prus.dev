<div x-data="{ showFilters: false, viewMode: 'grid' }" class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <x-container class="py-8 lg:py-12">
        <!-- Enhanced Breadcrumbs -->
        <div class="mb-8">
            <x-breadcrumbs :items="[
                ['label' => __('translations.categories'), 'url' => route('localized.categories.index', ['locale' => app()->getLocale()])],
                ['label' => $category->name, 'url' => url()->current()],
            ]" />
        </div>

        <!-- Enhanced Header Section -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 lg:p-8">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">{{ $category->name }}</h1>
                        </div>
                        @if (!empty($category->description))
                            <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed max-w-3xl">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- View Mode Toggle -->
                        <div class="hidden lg:flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                            <button @click="viewMode = 'grid'" 
                                    :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-500 dark:text-gray-400'"
                                    class="p-2 rounded-md transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button @click="viewMode = 'list'" 
                                    :class="viewMode === 'list' ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-500 dark:text-gray-400'"
                                    class="p-2 rounded-md transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                        <!-- Mobile Filter Button -->
                        <div class="lg:hidden">
                            <button @click="showFilters = true" type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z" />
                                </svg>
                                {{ __('translations.filter') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Enhanced Results Counter -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-lg">
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">
                                {{ __('translations.showing') ?? 'Showing' }} {{ $products->firstItem() }}–{{ $products->lastItem() }} {{ __('translations.of_total') ?? 'of' }} {{ $products->total() }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $products->total() }} {{ __('translations.products') ?? 'Products' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $roots = \App\Models\Category::query()
                ->where('is_visible', true)
                ->whereNull('parent_id')
                ->with([
                    'children' => function ($q) {
                        $q->where('is_visible', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->with([
                                'children' => function ($qq) {
                                    $qq->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
                                },
                            ]);
                    },
                ])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $categoryTree = $roots
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'children' => $cat->children
                            ->map(function ($child) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'children' => $child->children
                                        ->map(function ($gc) {
                                            return [
                                                'id' => $gc->id,
                                                'name' => $gc->name,
                                                'slug' => $gc->slug,
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <aside class="lg:col-span-3">
                <div class="hidden lg:block sticky top-24 space-y-6">
                    <!-- Enhanced Categories Filter -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ __('translations.categories') }}
                            </h3>
                        </div>
                        <div class="p-4">
                            <x-category.tree :nodes="$categoryTree" />
                        </div>
                    </div>

                    <!-- Enhanced Advanced Filters -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z"></path>
                                </svg>
                                {{ __('translations.advanced_filters') }}
                            </h3>
                        </div>
                        <div class="p-4">
                            @livewire('components.product-filter-widget')
                        </div>
                    </div>
                </div>

                <!-- Enhanced Mobile off-canvas -->
                <div x-cloak x-show="showFilters" class="lg:hidden fixed inset-0 z-50">
                    <div @click="showFilters = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                    <div
                         class="absolute inset-y-0 left-0 w-80 max-w-[85%] overflow-y-auto bg-white dark:bg-gray-900 shadow-2xl">
                        <!-- Mobile Header -->
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z"></path>
                                    </svg>
                                    {{ __('translations.filters') ?? __('translations.advanced_filters') }}
                                </h3>
                                <button @click="showFilters = false"
                                        class="rounded-lg p-2 text-white/80 hover:bg-white/20 transition-colors duration-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 space-y-6">
                            <div>
                                <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    {{ __('translations.categories') }}
                                </h4>
                                <x-category.tree :nodes="$categoryTree" />
                            </div>
                            @livewire('components.product-filter-widget')
                        </div>
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-9">
                <!-- Enhanced Sort and Filter Controls -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg p-4 mb-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ $products->total() }} {{ __('translations.products') ?? 'Products' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('translations.sort_by') ?? 'Sort by' }}:</label>
                                <select wire:model.live="sortBy"
                                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-900 dark:text-white px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="created_at">{{ __('translations.newest') ?? 'Newest' }}</option>
                                    <option value="name">{{ __('translations.name') ?? 'Name' }}</option>
                                    <option value="price">{{ __('translations.price') ?? 'Price' }}</option>
                                    <option value="rating">{{ __('translations.rating') ?? 'Rating' }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('translations.order') ?? 'Order' }}:</label>
                                <select wire:model.live="sortDirection"
                                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-900 dark:text-white px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="asc">{{ __('translations.ascending') ?? 'Ascending' }}</option>
                                    <option value="desc">{{ __('translations.descending') ?? 'Descending' }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($products->count() > 0)
                    <!-- Enhanced Products Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" 
                         :class="viewMode === 'list' ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'">
                        @foreach ($products as $product)
                            <div class="animate-fade-in-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                                <x-product-card :product="$product" :show-quick-add="true" :show-wishlist="true" :show-compare="true" />
                            </div>
                        @endforeach
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="mt-12 flex justify-center">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg p-4">
                            <x-filament::pagination :paginator="$products" />
                        </div>
                    </div>
                @else
                    <!-- Enhanced Empty State -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center shadow-lg">
                        <div class="mx-auto mb-6 h-16 w-16 text-gray-400 dark:text-gray-500">
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ __('translations.no_products_found') ?? 'No products found' }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
                            {{ __('translations.try_different_search') ?? 'Try adjusting your filters or search terms to find what you\'re looking for.' }}
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ __('translations.browse_categories') ?? 'Browse Categories' }}
                            </a>
                            <a href="{{ route('products.index') }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ __('translations.view_all_products') ?? 'View All Products' }}
                            </a>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <!-- Enhanced Back Button -->
        <div class="mt-12 text-center">
            <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('frontend.buttons.back_to_categories') ?? 'Back to Categories' }}
            </a>
        </div>
    </x-container>
</div>

<!-- Enhanced CSS Animations -->
<style>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Enhanced focus styles */
.focus\:ring-2:focus {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

/* Glassmorphism effect */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

.backdrop-blur-md {
    backdrop-filter: blur(12px);
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:-translate-y-1 {
    transform: translateY(-0.25rem);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-track {
    background: #1e293b;
}

.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>
