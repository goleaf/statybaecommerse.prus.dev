@section('meta')
    <x-meta
        :title="__('translations.brands') . ' - ' . config('app.name')"
        :description="__('Browse all our trusted brand partners and discover quality products')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Page Header --}}
    <x-shared.page-header
        title="{{ __('shared.brands') }}"
        description="{{ __('Browse all our trusted brand partners and discover quality products') }}"
        icon="heroicon-o-tag"
        :breadcrumbs="[
            ['title' => __('shared.home'), 'url' => route('home')],
            ['title' => __('shared.brands')]
        ]"
    />

    {{-- Filters Section --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Search --}}
                <div class="flex-1 max-w-md">
                    <label for="search" class="sr-only">{{ __('Search brands') }}</label>
                    <div class="relative">
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            id="search"
                            placeholder="{{ __('Search brands...') }}"
                            class="block w-full rounded-lg border-gray-300 bg-white px-4 py-2 pl-10 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Sort Options --}}
                <div class="flex items-center gap-4">
                    <label for="sortBy" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sort by') }}:</label>
                    <select
                        wire:model.live="sortBy"
                        id="sortBy"
                        class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="name">{{ __('Name') }}</option>
                        <option value="products_count">{{ __('Most Products') }}</option>
                        <option value="created_at">{{ __('Newest') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Brands Grid --}}
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if($this->brands->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->brands as $brand)
                    <x-shared.card hover="true" class="group">
                        <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg">
                            @if($brand->getFirstMediaUrl('logo'))
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                    alt="{{ $brand->name }}"
                                    class="h-48 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-6"
                                    loading="lazy"
                                />
                            @else
                                <div class="flex h-48 items-center justify-center bg-gray-100 dark:bg-gray-700">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                            <a href="{{ route('brands.show', $brand) }}" class="stretched-link">
                                {{ $brand->name }}
                            </a>
                        </h3>
                        
                        @if($brand->description)
                            <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ $brand->description }}
                            </p>
                        @endif
                        
                        <x-slot name="footer">
                            <div class="flex items-center justify-between">
                                <x-shared.badge variant="primary" size="sm">
                                    {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                </x-shared.badge>
                                
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </x-slot>
                    </x-shared.card>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($this->brands->hasPages())
                <div class="mt-12">
                    <x-shared.pagination :paginator="$this->brands" />
                </div>
            @endif
        @else
            <x-shared.empty-state
                title="{{ __('shared.no_results_found') }}"
                :description="!empty($this->search) ? __('Try adjusting your search terms') : __('No brands are available at the moment')"
                icon="heroicon-o-cube"
                :action-text="!empty($this->search) ? __('shared.clear_filters') : null"
                :action-wire="!empty($this->search) ? '$set(\'search\', \'\')' : null"
            />
        @endif
    </div>
</div>
