@section('meta')
    <x-meta
        :title="$brand->getTranslatedName() . ' - ' . config('app.name')"
        :description="$brand->getTranslatedDescription()"
        canonical="{{ url()->current() }}" />
@endsection

<div class="min-h-screen bg-sage">
    {{-- Hero Section --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-dark via-brand-primary to-brand-primary-light">
        <div class="absolute inset-0 bg-black/30"></div>
        
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-pattern-dots-60"></div>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            {{-- Breadcrumbs --}}
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-sage hover:text-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            {{ __('shared.home') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-sage/70" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}" class="ml-1 text-sage hover:text-white md:ml-2">
                                {{ __('shared.brands') }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-sage/70" fill="currentColor" viewBox="0 0 20 20">
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
                        <p class="text-xl text-sage mb-8 max-w-2xl">
                            {{ $brand->getTranslatedDescription() }}
                        </p>
                    @endif
                    
                    @if($brand->website)
                    <div class="flex items-center text-sage mb-8">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                        </svg>
                        <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="font-medium text-white hover:text-sage transition-colors">
                            {{ $brand->website }}
                        </a>
                    </div>
                    @endif

                    {{-- Brand Stats --}}
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-white">{{ $products->count() }}</div>
                            <div class="text-sage">{{ __('frontend/brands.stats.products_available') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-white">{{ __('frontend/brands.show.premium') }}</div>
                            <div class="text-sage">{{ __('frontend/brands.show.quality') }}</div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        @if($brand->website)
                            <a href="{{ $brand->website }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-dark bg-sage hover:bg-sage/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sage transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                {{ __('frontend/brands.show.visit_website') }}
                            </a>
                        @endif
                        
                        <a href="#products" class="inline-flex items-center px-6 py-3 border border-sage text-base font-medium rounded-md text-sage hover:bg-sage/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sage transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            {{ __('frontend/brands.show.view_products') }}
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
                        <div class="h-96 w-full bg-gradient-to-br from-brand-primary to-brand-primary-light rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-center text-white">
                                <div class="w-24 h-24 bg-sage/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <span class="text-4xl font-bold text-sage">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
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
            <h2 class="text-3xl font-bold text-dark mb-4">
                {{ __('frontend/brands.show.products_by_brand', ['brand' => $brand->getTranslatedName()]) }}
            </h2>
            <p class="text-lg text-brand-primary-light max-w-2xl mx-auto">
                {{ __('frontend/brands.show.collection_intro', ['brand' => $brand->getTranslatedName()]) }}
            </p>
        </div>

        @if($products->count() > 0)
            <x-shared.sidebar-layout
                sidebarWidth="w-full lg:w-72"
                contentWidth="flex-1"
                sidebarClass="lg:pr-10"
                contentClass="space-y-10"
            >
                <x-slot name="sidebar">
                    <x-shared.filter-sidebar
                        title="{{ __('frontend/brands.show.explore_highlights', ['brand' => $brand->getTranslatedName()]) }}"
                        description="{{ __('frontend/brands.show.switch_views') }}"
                    >
                        <div class="space-y-3">
                            <button type="button" class="flex w-full items-center justify-between rounded-xl border border-sage bg-sage px-4 py-2 text-sm font-medium text-dark transition hover:bg-sage/80">
                                <span>{{ __('frontend/brands.show.all_products') }}</span>
                                <span class="rounded-full bg-dark/10 px-2 py-0.5 text-xs font-semibold text-dark">{{ $products->count() }}</span>
                            </button>
                            <button type="button" class="flex w-full items-center justify-between rounded-xl border border-brand-primary-lighter bg-white px-4 py-2 text-sm font-medium text-brand-primary-light transition hover:border-sage hover:text-dark">
                                <span>{{ __('frontend/brands.show.new_arrivals') }}</span>
                                <svg class="h-4 w-4 text-brand-primary-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </button>
                            <button type="button" class="flex w-full items-center justify-between rounded-xl border border-brand-primary-lighter bg-white px-4 py-2 text-sm font-medium text-brand-primary-light transition hover:border-sage hover:text-dark">
                                <span>{{ __('frontend/brands.show.best_sellers') }}</span>
                                <svg class="h-4 w-4 text-brand-primary-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>

                        <x-slot name="footer">
                            @if($brand->products()->where('is_visible', true)->whereNotNull('published_at')->count() > 12)
                                <x-shared.button
                                    href="{{ route('localized.products.index', ['locale' => app()->getLocale(), 'brand' => $brand->getTranslatedSlug()]) }}"
                                    variant="primary"
                                    size="sm"
                                    class="w-full"
                                >
                                    {{ __('frontend/brands.show.view_all_products') }}
                                </x-shared.button>
                            @endif
                        </x-slot>
                    </x-shared.filter-sidebar>
                </x-slot>

                <div class="space-y-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3 text-sm text-brand-primary-light">
                            <span class="inline-flex items-center gap-2 rounded-full bg-sage px-3 py-1 text-xs font-semibold text-dark">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M9 7h6m-3 0v14" />
                                </svg>
                                {{ trans_choice('frontend/brands.products', $products->count(), ['count' => $products->count()]) }}
                            </span>
                            <span>{{ __('frontend/brands.show.updated_weekly') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach($products as $product)
                            <x-shared.product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </x-shared.sidebar-layout>
        @else
            <x-shared.empty-state
                title="{{ __('frontend/brands.show.empty_title') }}"
                description="{{ __('frontend/brands.show.empty_desc') }}"
                icon="heroicon-o-cube"
            />
        @endif
    </div>

    {{-- Brand Information Section merged into hero to avoid duplication --}}

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
        <div class="bg-brand-primary-lighter/20">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-dark mb-4">{{ __('frontend/brands.show.other_brands') }}</h2>
                    <p class="text-lg text-brand-primary-light">{{ __('frontend/brands.show.discover_more') }}</p>
                </div>
                
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedBrands as $relatedBrand)
                        <x-shared.card hover="true" class="group !bg-white dark:!bg-white text-dark border border-ash/30 hover:border-ash/60 relative overflow-hidden shadow-sm hover:shadow-md">
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-t-lg bg-white">
                                @if($relatedBrand->getFirstMediaUrl('logo'))
                                    <img 
                                        src="{{ $relatedBrand->getFirstMediaUrl('logo') }}" 
                                        alt="{{ $relatedBrand->getTranslatedName() }}"
                                        loading="lazy"
                                        class="h-32 w-full object-contain object-center transition-transform duration-300 group-hover:scale-105 p-4"
                                    />
                                @else
                                    <div class="flex h-32 items-center justify-center bg-ash/10" aria-hidden="true">
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-ash/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                                                <span class="text-lg font-bold text-dark">{{ strtoupper(substr($relatedBrand->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="text-xs text-stone">{{ $relatedBrand->name }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-dark group-hover:text-stone transition-colors duration-200 mb-2">
                                    <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $relatedBrand->getTranslatedSlug()]) }}" class="stretched-link">
                                        {{ $relatedBrand->getTranslatedName() }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center justify-between">
                                    <x-shared.badge variant="primary" size="sm">
                                        {{ trans_choice('frontend/brands.products', $relatedBrand->products_count, ['count' => $relatedBrand->products_count]) }}
                                    </x-shared.badge>
                                    
                                    <svg class="h-4 w-4 text-stone group-hover:text-dark transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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