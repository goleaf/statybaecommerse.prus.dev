@section('meta')
    <x-meta
        :title="__('translations.brands') . ' - ' . config('app.name')"
        :description="__('Browse all our trusted brand partners and discover quality products')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 dark:from-gray-900 dark:via-blue-900 dark:to-indigo-900">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/90 to-transparent"></div>
        
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('shared.brands') }}
                </h1>
                
                <p class="mt-6 text-xl text-blue-100 max-w-3xl mx-auto">
                    {{ __('Browse all our trusted brand partners and discover quality products from industry leaders') }}
                </p>
                
                {{-- Stats --}}
                <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ $this->brands->total() }}</div>
                        <div class="text-blue-100">{{ __('Total Brands') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ $this->brands->sum('products_count') }}</div>
                        <div class="text-blue-100">{{ __('Products Available') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ __('Premium Quality') }}</div>
                        <div class="text-blue-100">{{ __('Guaranteed') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Search and Filters --}}
                <div class="flex-1">
                    <form wire:submit.prevent class="flex flex-col sm:flex-row gap-4">
                {{ $this->form }}
            </form>
                </div>
                
                {{-- View Toggle --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('View:') }}</span>
                    <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button type="button" class="p-2 rounded-md bg-white dark:bg-gray-600 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button type="button" class="p-2 rounded-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Brands Grid --}}
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        @if($this->brands->count() > 0)
            {{-- Featured Brands Section --}}
            @if($this->brands->where('is_featured', true)->count() > 0)
                <div class="mb-16">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Featured Brands') }}</h2>
                            <p class="text-gray-600 dark:text-gray-300">{{ __('Our most popular and trusted brand partners') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-gray-500">{{ $this->brands->where('is_featured', true)->count() }} {{ __('brands') }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->brands->where('is_featured', true)->take(6) as $brand)
                            <x-shared.card hover="true" class="group relative overflow-hidden">
                                {{-- Featured Badge --}}
                                <div class="absolute top-4 right-4 z-10">
                                    <x-shared.badge variant="primary" size="sm" class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        {{ __('Featured') }}
                                    </x-shared.badge>
                                </div>
                                
                                <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                            @if($brand->getFirstMediaUrl('logo'))
                                <img 
                                    src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                    alt="{{ $brand->name }}"
                                    loading="lazy"
                                            class="h-48 w-full object-contain object-center transition-transform duration-500 group-hover:scale-110 p-8"
                                />
                            @else
                                        <div class="flex h-48 items-center justify-center" aria-hidden="true">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                    <span class="text-2xl font-bold text-white">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 mb-2">
                                        <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                            {{ $brand->getTranslatedName() }}
                                        </a>
                                    </h3>
                                    
                                    @if($brand->getTranslatedDescription())
                                        <p class="text-gray-600 dark:text-gray-300 line-clamp-2 mb-4">
                                            {{ $brand->getTranslatedDescription() }}
                                        </p>
                                    @endif
                                    
                                    <div class="flex items-center justify-between">
                                        <x-shared.badge variant="primary" size="sm" class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                        </x-shared.badge>
                                        
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                            {{ __('Premium Quality') }}
                                        </div>
                                    </div>
                                </div>
                            </x-shared.card>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- All Brands Section --}}
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('All Brands') }}</h2>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Discover all our brand partners') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-500">{{ $this->brands->count() }} {{ __('brands') }}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-live="polite">
                    @foreach($this->brands as $brand)
                        <x-shared.card hover="true" class="group relative overflow-hidden">
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                @if($brand->getFirstMediaUrl('logo'))
                                    <img 
                                        src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $brand->name }}"
                                        loading="lazy"
                                        class="h-32 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-6"
                                    />
                                @else
                                    <div class="flex h-32 items-center justify-center" aria-hidden="true">
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                                                <span class="text-lg font-bold text-white">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $brand->name }}</div>
                                        </div>
                                </div>
                            @endif
                        </div>
                        
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 mb-2">
                            <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                {{ $brand->getTranslatedName() }}
                            </a>
                        </h3>
                        
                        @if($brand->getTranslatedDescription())
                                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 mb-3">
                                {{ $brand->getTranslatedDescription() }}
                            </p>
                        @endif
                        
                            <div class="flex items-center justify-between">
                                <x-shared.badge variant="primary" size="sm">
                                    {{ $brand->products_count }} {{ trans_choice('products', $brand->products_count) }}
                                </x-shared.badge>
                                
                                    <svg class="h-4 w-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                </div>
                            </div>
                    </x-shared.card>
                @endforeach
                </div>
            </div>

            {{-- Pagination --}}
            @if($this->brands->hasPages())
                <div class="mt-16">
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

    {{-- CTA Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-gray-800 dark:to-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white mb-4">{{ __('Can\'t find your brand?') }}</h2>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    {{ __('We\'re always looking to partner with new brands. Contact us to discuss partnership opportunities.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Contact Us') }}
                    </a>
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('Browse Products') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>