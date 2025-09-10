<div x-data="{ showFilters: false }">
    <x-container class="py-6 lg:py-8">
        <x-breadcrumbs :items="[
            ['label' => __('translations.categories'), 'url' => route('categories.index')],
            ['label' => $category->name, 'url' => url()->current()],
        ]" />

        <div class="mb-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white">{{ $category->name }}
                    </h1>
                    @if (!empty($category->description))
                        <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">{{ $category->description }}</p>
                    @endif
                </div>
                <div class="lg:hidden">
                    <button @click="showFilters = true" type="button"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13v6l-4-2v-4a1 1 0 00-.293-.707L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>
                        {{ __('translations.filter') }}
                    </button>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('translations.showing') ?? '' }}
                {{ $products->firstItem() }}–{{ $products->lastItem() }} {{ __('translations.of_total') }}
                {{ $products->total() }}</div>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <aside class="lg:col-span-3">
                <div class="hidden lg:block sticky top-24 space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('translations.categories') }}</h3>
                        <x-category.tree :nodes="$categoryTree" />
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('translations.advanced_filters') }}</h3>
                        @livewire('components.product-filter-widget')
                    </div>
                </div>

                <!-- Mobile off-canvas -->
                <div x-cloak x-show="showFilters" class="lg:hidden fixed inset-0 z-50">
                    <div @click="showFilters = false" class="absolute inset-0 bg-black/40"></div>
                    <div
                         class="absolute inset-y-0 left-0 w-80 max-w-[85%] overflow-y-auto bg-white p-4 shadow-xl dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('translations.filters') ?? __('translations.advanced_filters') }}</h3>
                            <button @click="showFilters = false"
                                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                                <x-untitledui-x class="h-5 w-5" />
                            </button>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('translations.categories') }}</h4>
                                <x-category.tree :nodes="$categoryTree" />
                            </div>
                            @livewire('components.product-filter-widget')
                        </div>
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-9">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $products->total() }} {{ __('translations.products') ?? __('Products') }}
                    </div>
                    <div class="flex items-center gap-3">
                        <select wire:model.live="sortBy"
                                class="rounded-md border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="created_at">{{ __('translations.newest') }}</option>
                            <option value="name">{{ __('translations.name') }}</option>
                            <option value="price">{{ __('translations.price') }}</option>
                            <option value="rating">{{ __('translations.rating') }}</option>
                        </select>
                        <select wire:model.live="sortDirection"
                                class="rounded-md border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="asc">{{ __('translations.ascending') }}</option>
                            <option value="desc">{{ __('translations.descending') }}</option>
                        </select>
                    </div>
                </div>

                @if ($products->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" :show-quick-add="true" :show-wishlist="true" :show-compare="true" />
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-center">
                        <x-filament::pagination :paginator="$products" />
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                        <div class="mx-auto mb-3 h-10 w-10 text-gray-400">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('translations.no_products_found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('translations.try_different_search') }}</p>
                        <div class="mt-6 flex items-center justify-center gap-3">
                            <a href="{{ route('categories.index') }}"
                               class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">{{ __('translations.browse_categories') }}</a>
                            <a href="{{ route('products.index') }}"
                               class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ __('translations.view_all_products') }}</a>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </x-container>
</div>
