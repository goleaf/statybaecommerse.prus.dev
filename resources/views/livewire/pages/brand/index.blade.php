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
            <form wire:submit.prevent class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{ $this->form }}
            </form>
        </div>
    </div>

    {{-- Brands Grid --}}
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if($this->brands->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-live="polite">
                @foreach($this->brands as $brand)
                    <x-shared.card hover="true" class="group">
                        <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg">
                            @if($brand->getFirstMediaUrl('logo'))
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                    alt="{{ $brand->name }}"
                                    loading="lazy"
                                    class="h-48 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-6"
                                />
                            @else
                                <div class="flex h-48 items-center justify-center bg-gray-100 dark:bg-gray-700" aria-hidden="true">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                            <a href="{{ route('localized.brands.show', $brand->getTranslatedSlug()) }}" class="stretched-link">
                                {{ $brand->getTranslatedName() }}
                            </a>
                        </h3>
                        
                        @if($brand->getTranslatedDescription())
                            <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ $brand->getTranslatedDescription() }}
                            </p>
                        @endif
                        
                        <x-slot name="footer">
                            <div class="flex items-center justify-between">
                                <x-shared.badge variant="primary" size="sm">
                                    {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                </x-shared.badge>
                                
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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

    <x-filament-actions::modals />
</div>
