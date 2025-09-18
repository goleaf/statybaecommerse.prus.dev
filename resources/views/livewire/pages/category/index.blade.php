@section('meta')
    <x-meta
            :title="__('Categories') . ' - ' . config('app.name')"
            :description="__('Explore our comprehensive range of categories')"
            canonical="{{ url()->current() }}" />
@endsection

<div>
    <x-container class="py-6 md:py-10">
        <nav class="text-sm text-muted-700 mb-3 md:mb-6" aria-label="Breadcrumb">
            <ol class="list-reset flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-gray-900">{{ __('Home') }}</a></li>
                <li class="text-gray-400" aria-hidden="true">/</li>
                <li aria-current="page" class="text-gray-700 font-medium">{{ __('Categories') }}</li>
            </ol>
        </nav>

        <div class="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-slate-50 to-white p-6 md:p-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-4xl font-bold tracking-tight text-gray-900">{{ __('Categories') }}</h1>
                    <p class="mt-1 text-gray-600">{{ __('Explore our comprehensive range of categories') }}</p>
                </div>
                <div class="flex items-center gap-2 md:gap-3">
                    <div class="flex-1 md:flex-none md:w-80">
                        <label for="category-search" class="sr-only">{{ __('Search categories') }}</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m21 21-4.35-4.35M11 19a 8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                            </svg>
                            <input id="category-search" type="search" wire:model.live.debounce.400ms="search"
                                   placeholder="{{ __('Search categories...') }}"
                                   class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2 md:py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                    </div>
                    <div class="hidden md:flex items-center gap-3">
                        <label class="text-sm text-gray-600">{{ __('Sort') }}</label>
                        <select wire:model.live="sort" class="rounded-lg border-gray-300 text-sm">
                            <option value="name_asc">{{ __('Name (A–Z)') }}</option>
                            <option value="name_desc">{{ __('Name (Z–A)') }}</option>
                            <option value="products_desc">{{ __('Most products') }}</option>
                            <option value="products_asc">{{ __('Fewest products') }}</option>
                        </select>
                        <label class="text-sm text-gray-600">{{ __('Per page') }}</label>
                        <select wire:model.live="perPage" class="rounded-lg border-gray-300 text-sm">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="36">36</option>
                            <option value="48">48</option>
                        </select>
                    </div>
                    <button type="button" wire:click="$toggle('sidebarOpen')"
                            wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}"
                            class="md:hidden inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-sm shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 6h18M6 12h12M10 18h8" />
                        </svg>
                        {{ __('Filters') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Sidebar desktop -->
            <aside class="md:col-span-3 lg:col-span-3 hidden md:block">
                <div class="sticky top-24">
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl border p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold">{{ __('Filters') }}</h3>
                                <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                                   class="text-sm text-gray-600 hover:underline">{{ __('Clear all') }}</a>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="inStock"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('In stock') }}</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="onSale"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('On sale') }}</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="hasProducts"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('With products') }}</span>
                                </label>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <input type="number" min="0" step="0.01"
                                       wire:model.live.debounce.500ms="priceMin"
                                       class="rounded-lg border-gray-300 text-sm"
                                       placeholder="{{ __('Min price') }}" />
                                <input type="number" min="0" step="0.01"
                                       wire:model.live.debounce.500ms="priceMax"
                                       class="rounded-lg border-gray-300 text-sm"
                                       placeholder="{{ __('Max price') }}" />
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border p-4 shadow-sm">
                            <h3 class="text-sm font-semibold mb-3">{{ __('Brands') }}</h3>
                            <div class="space-y-2 max-h-64 overflow-auto pr-1">
                                @foreach ($this->facetBrands as $brand)
                                    <label class="flex items-center justify-between gap-2 text-sm">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" value="{{ $brand['id'] }}"
                                                   wire:model.live="selectedBrandIds"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span>{{ $brand['name'] }}</span>
                                        </div>
                                        <span
                                              class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $brand['count'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border p-4 shadow-sm">
                            <h3 class="text-sm font-semibold mb-3">{{ __('Collections') }}</h3>
                            <div class="space-y-2 max-h-64 overflow-auto pr-1">
                                @foreach ($this->facetCollections as $collection)
                                    <label class="flex items-center justify-between gap-2 text-sm">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" value="{{ $collection['id'] }}"
                                                   wire:model.live="selectedCollectionIds"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span>{{ $collection['name'] }}</span>
                                        </div>
                                        <span
                                              class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $collection['count'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border p-4 shadow-sm">
                            <h3 class="text-sm font-semibold mb-3">{{ __('Categories') }}</h3>
                            <div class="space-y-2 max-h-64 overflow-auto pr-1">
                                @foreach ($this->facetCategories as $cat)
                                    <label class="flex items-center justify-between gap-2 text-sm">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" value="{{ $cat['id'] }}"
                                                   wire:model.live="selectedCategoryIds"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                            <span>{{ $cat['name'] }}</span>
                                        </div>
                                        <span
                                              class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $cat['count'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile filters drawer -->
            @if ($sidebarOpen)
                <div class="fixed inset-0 z-40 md:hidden">
                    <div class="absolute inset-0 bg-black/30" aria-hidden="true" wire:click="$toggle('sidebarOpen')" wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}">
                    </div>
                    <div class="absolute inset-y-0 left-0 w-11/12 max-w-sm bg-white shadow-xl p-4 overflow-y-auto">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-base font-semibold">{{ __('Filters') }}</h3>
                            <button type="button" class="p-2 rounded-lg hover:bg-gray-100"
                                    wire:click="$toggle('sidebarOpen')" wire:confirm="{{ __('translations.confirm_toggle_sidebar') }}" aria-label="{{ __('Close') }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                           class="text-sm text-gray-600 hover:underline">{{ __('Clear all') }}</a>

                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="inStock"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('In stock') }}</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="onSale"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('On sale') }}</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="hasProducts"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ __('With products') }}</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" min="0" step="0.01"
                                       wire:model.live.debounce.500ms="priceMin"
                                       class="rounded-lg border-gray-300 text-sm"
                                       placeholder="{{ __('Min price') }}" />
                                <input type="number" min="0" step="0.01"
                                       wire:model.live.debounce.500ms="priceMax"
                                       class="rounded-lg border-gray-300 text-sm"
                                       placeholder="{{ __('Max price') }}" />
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">{{ __('Brands') }}</h4>
                                <div class="space-y-2 max-h-48 overflow-auto pr-1">
                                    @foreach ($this->facetBrands as $brand)
                                        <label class="flex items-center justify-between gap-2 text-sm">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" value="{{ $brand['id'] }}"
                                                       wire:model.live="selectedBrandIds"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                <span>{{ $brand['name'] }}</span>
                                            </div>
                                            <span
                                                  class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $brand['count'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">{{ __('Collections') }}</h4>
                                <div class="space-y-2 max-h-48 overflow-auto pr-1">
                                    @foreach ($this->facetCollections as $collection)
                                        <label class="flex items-center justify-between gap-2 text-sm">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" value="{{ $collection['id'] }}"
                                                       wire:model.live="selectedCollectionIds"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                <span>{{ $collection['name'] }}</span>
                                            </div>
                                            <span
                                                  class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $collection['count'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold mb-2">{{ __('Categories') }}</h4>
                                <div class="space-y-2 max-h-48 overflow-auto pr-1">
                                    @foreach ($this->facetCategories as $cat)
                                        <label class="flex items-center justify-between gap-2 text-sm">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" value="{{ $cat['id'] }}"
                                                       wire:model.live="selectedCategoryIds"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                <span>{{ $cat['name'] }}</span>
                                            </div>
                                            <span
                                                  class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $cat['count'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Results -->
            <main class="md:col-span-9 lg:col-span-9">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm text-gray-600">{{ $this->categories->total() }} {{ __('results') }}</p>
                    <div class="hidden md:flex items-center gap-2">
                        <span class="text-sm text-gray-500">{{ __('Showing') }}</span>
                        <select wire:model.live="perPage" class="rounded-lg border-gray-300 text-sm">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="36">36</option>
                            <option value="48">48</option>
                        </select>
                    </div>
                </div>

                @if ($this->categories->count() > 0)
                    <div class="relative" aria-live="polite">
                        <div wire:loading.delay
                             class="absolute inset-0 z-10 rounded-xl bg-white/70 backdrop-blur-sm flex items-center justify-center">
                            <div
                                 class="h-5 w-5 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                            @foreach ($this->categories as $category)
                                <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category->slug ?? $category]) }}"
                                   class="group relative overflow-hidden rounded-xl border bg-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                        @php
                                            $imgMd =
                                                $category->getFirstMediaUrl('images', 'image-md') ?:
                                                $category->getFirstMediaUrl('images');
                                            $imgSm = $category->getFirstMediaUrl('images', 'image-sm') ?: $imgMd;
                                            $imgXs = $category->getFirstMediaUrl('images', 'image-xs') ?: $imgSm;
                                            $imgLg = $category->getFirstMediaUrl('images', 'image-lg') ?: $imgMd;
                                        @endphp
                                        @if ($imgMd)
                                            <img
                                                 src="{{ $imgMd }}"
                                                 srcset="{{ $imgXs }} 150w, {{ $imgSm }} 300w, {{ $imgMd }} 500w, {{ $imgLg }} 800w"
                                                 sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                                                 alt="{{ $category->name }}"
                                                 loading="lazy"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                            <div
                                                 class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent">
                                            </div>
                                            <div
                                                 class="absolute left-2 top-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-xs font-medium text-gray-700 shadow-sm">
                                                <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                                                </svg>
                                                <span>{{ $category->products_count }}</span>
                                            </div>
                                        @else
                                            <div
                                                 class="w-full h-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center">
                                                <span
                                                      class="text-white text-2xl font-bold">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-semibold text-white drop-shadow-sm">
                                                {{ $category->name }}</h3>
                                            <span
                                                  class="inline-flex items-center justify-center rounded-full bg-white/90 text-gray-900 group-hover:bg-white px-2 py-1 text-[11px] font-medium shadow-sm">
                                                {{ __('View') }}
                                                <svg class="ml-1 h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </span>
                                        </div>
                                        @if ($category->description)
                                            <p class="mt-1 line-clamp-2 text-xs text-white/90">
                                                {{ $category->description }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8">
                        {{ $this->categories->links() }}
                    </div>
                @else
                    <div class="rounded-2xl border bg-white p-10 text-center">
                        <div
                             class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                            📂</div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('No categories available') }}</h3>
                        <p class="mt-1 text-gray-500">{{ __('Categories will appear here once they are added') }}</p>
                    </div>
                @endif
            </main>
        </div>
    </x-container>

    <x-filament-actions::modals />
</div>
