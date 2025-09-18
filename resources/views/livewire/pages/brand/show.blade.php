@section('meta')
    <x-meta
        :title="$brand->getTranslatedName() . ' - ' . config('app.name')"
        :description="$brand->getTranslatedDescription()"
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
            {{-- Breadcrumbs --}}
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-blue-100 hover:text-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            {{ __('shared.home') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" class="ml-1 text-blue-100 hover:text-white md:ml-2">
                                {{ __('shared.brands') }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-1 text-white md:ml-2">{{ $brand->getTranslatedName() }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
<div>
                    {{-- Brand Logo --}}
                    @if($brand->getFirstMediaUrl('logo'))
        <div class="mb-8">
                            <img 
                                src="{{ $brand->getFirstMediaUrl('logo') }}" 
                                alt="{{ $brand->getTranslatedName() }}"
                                class="h-24 w-auto object-contain"
                            />
                        </div>
                    @endif
                    
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6">
                        {{ $brand->getTranslatedName() }}
            </h1>
            
                    @if($brand->getTranslatedDescription())
                        <p class="text-xl text-blue-100 mb-8 max-w-2xl">
                            {{ $brand->getTranslatedDescription() }}
                        </p>
                    @endif
                    
                    {{-- Brand Stats --}}
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-white">{{ $products->count() }}</div>
                            <div class="text-blue-100">{{ __('Products Available') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-white">{{ __('Premium') }}</div>
                            <div class="text-blue-100">{{ __('Quality') }}</div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        @if($brand->website)
                            <a href="{{ $brand->website }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                {{ __('Visit Website') }}
                            </a>
                        @endif
                        
                        <a href="#products" class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            {{ __('View Products') }}
                        </a>
                    </div>
                </div>
                
                {{-- Brand Banner/Image --}}
                @if($brand->getFirstMediaUrl('banner'))
                    <div class="relative">
                        <img 
                            src="{{ $brand->getFirstMediaUrl('banner') }}" 
                            alt="{{ $brand->getTranslatedName() }}"
                            class="h-96 w-full object-cover rounded-2xl shadow-2xl"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent rounded-2xl"></div>
                    </div>
                @else
                    <div class="relative">
                        <div class="h-96 w-full bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-center text-white">
                                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <span class="text-4xl font-bold">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                </div>
                                <div class="text-2xl font-bold">{{ $brand->getTranslatedName() }}</div>
                            </div>
                        </div>
                </div>
            @endif
            </div>
        </div>
        </div>

    {{-- Products Section --}}
    <div id="products" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('Products by :brand', ['brand' => $brand->getTranslatedName()]) }}
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                {{ __('Discover our complete collection of :brand products, carefully curated for quality and performance.', ['brand' => $brand->getTranslatedName()]) }}
            </p>
        </div>

        @if($products->count() > 0)
            {{-- Product Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Filter by:') }}</span>
                        <div class="flex flex-wrap gap-2">
                            <button class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 transition-colors">
                                {{ __('All Products') }}
                            </button>
                            <button class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 transition-colors">
                                {{ __('New Arrivals') }}
                            </button>
                            <button class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 transition-colors">
                                {{ __('Best Sellers') }}
                            </button>
                        </div>
                    </div>
                    
                    @if($brand->products()->where('is_visible', true)->whereNotNull('published_at')->count() > 12)
                        <a href="{{ route('localized.products.index', ['locale' => app()->getLocale(), 'brand' => $brand->getTranslatedSlug()]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('View All Products') }}
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <x-shared.product-card :product="$product" />
                @endforeach
            </div>
                            @else
            <x-shared.empty-state
                title="{{ __('No products found') }}"
                description="{{ __('No products are available for this brand yet.') }}"
                icon="heroicon-o-cube"
            />
        @endif
    </div>

    {{-- Brand Information Section --}}
    @if($brand->getTranslatedDescription() || $brand->website)
        <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    {{-- Brand Story --}}
                    @if($brand->getTranslatedDescription())
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('About :brand', ['brand' => $brand->getTranslatedName()]) }}</h3>
                            <div class="prose max-w-none text-gray-600 dark:text-gray-300">
                                {!! nl2br(e($brand->getTranslatedDescription())) !!}
                            </div>
                        </div>
                    @endif
                    
                    {{-- Brand Details --}}
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('Brand Information') }}</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Brand Name:') }}</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $brand->getTranslatedName() }}</span>
                            </div>
                            
                            @if($brand->website)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                                    </svg>
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('Website:') }}</span>
                                    <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="ml-2 font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                        {{ $brand->website }}
                                    </a>
                                </div>
                            @endif
                            
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Products Available:') }}</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $products->count() }}</span>
                            </div>
                            
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">{{ __('Quality:') }}</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ __('Premium') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Related Brands --}}
    @php
        $relatedBrands = \App\Models\Brand::query()
            ->where('is_enabled', true)
            ->where('id', '!=', $brand->id)
            ->withCount('products')
            ->having('products_count', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    @endphp

    @if($relatedBrands->count() > 0)
        <div class="bg-gray-50 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Other Brands') }}</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300">{{ __('Discover more trusted brand partners') }}</p>
                </div>
                
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedBrands as $relatedBrand)
                        <x-shared.card hover="true" class="group relative overflow-hidden">
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                @if($relatedBrand->getFirstMediaUrl('logo'))
                                    <img 
                                        src="{{ $relatedBrand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $relatedBrand->getTranslatedName() }}"
                                        loading="lazy"
                                        class="h-32 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-4"
                                    />
                                @else
                                    <div class="flex h-32 items-center justify-center" aria-hidden="true">
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                                                <span class="text-lg font-bold text-white">{{ strtoupper(substr($relatedBrand->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $relatedBrand->name }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 mb-2">
                                    <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $relatedBrand->getTranslatedSlug()]) }}" class="stretched-link">
                                        {{ $relatedBrand->getTranslatedName() }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center justify-between">
                                    <x-shared.badge variant="primary" size="sm">
                                        {{ $relatedBrand->products_count }} {{ trans_choice('products', $relatedBrand->products_count) }}
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
        </div>
    @endif
</div>