@section('meta')
    <x-meta
        :title="__('frontend/brands.meta.title') . ' - ' . config('app.name')"
        :description="__('frontend/brands.meta.description')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    $paginator = $this->brands;
    $totalBrands = $paginator->total();
    $totalProducts = $paginator->sum('products_count');
    $activeFilterCount = collect([
        filled($search ?? ''),
        ($sortBy ?? 'name') !== 'name',
    ])->filter()->count();
    $featuredBrands = $paginator->where('is_featured', true);
@endphp

<main class="brands-page bg-sage text-gray-900" aria-label="{{ __('shared.brands') }}">
    <!-- Two Column Layout: Categories + Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 py-8">
            <!-- Left Sidebar: Brand Filters -->
            <aside class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">
                    <!-- Brand Filter Sidebar -->
                    <div class="bg-dark border border-sage/30 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-brand-primary flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                            <div>
                                <h2 class="text-lg font-semibold text-sage">{{ __('frontend/brands.sidebar.brand_filters') }}</h2>
                                <p class="text-sm text-ash">{{ __('frontend/brands.sidebar.refine_search') }}</p>
        </div>
    </div>

                        <form wire:submit.prevent class="space-y-6">
                            <div class="space-y-5">
                                <!-- Custom Search Field -->
                                <div class="space-y-2">
                                    <label for="search" class="block text-sm font-medium text-sage">{{ __('frontend/brands.sidebar.search_brands') }}</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input 
                                            type="text" 
                                            id="search"
                                            wire:model.live.debounce.300ms="search"
                                            placeholder="{{ __('frontend/brands.sidebar.search_placeholder') }}"
                                            class="block w-full pl-10 pr-3 py-2 border border-sage/30 bg-dark/80 text-sage placeholder-ash focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all duration-200"
                                        />
                                    </div>
                                </div>

                                <!-- Custom Sort Field -->
                                <div class="space-y-2">
                                    <label for="sortBy" class="block text-sm font-medium text-sage">{{ __('frontend/brands.sidebar.sort_by') }}</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                            </svg>
                                        </div>
                                        <select 
                                            id="sortBy"
                                            wire:model.live="sortBy"
                                            class="block w-full pl-10 pr-3 py-2 border border-sage/30 bg-dark/80 text-sage focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all duration-200 appearance-none"
                                        >
                                            <option value="name">{{ __('frontend/brands.sidebar.sort.name_asc') }}</option>
                                            <option value="name_desc">{{ __('frontend/brands.sidebar.sort.name_desc') }}</option>
                                            <option value="products_count">{{ __('frontend/brands.sidebar.sort.most_products') }}</option>
                                            <option value="created_at">{{ __('frontend/brands.sidebar.sort.newest') }}</option>
                                            <option value="featured">{{ __('frontend/brands.sidebar.sort.featured') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                            @if($activeFilterCount > 0)
                            <div class="mt-6 p-4 bg-brand-primary-lighter/20 rounded-xl border border-brand-primary-lighter">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-brand-primary">
                                        {{ $activeFilterCount === 1 ? __('frontend/brands.sidebar.active_filters_one') : __('frontend/brands.sidebar.active_filters_many', ['count' => $activeFilterCount]) }}
                                    </span>
                                    <button type="button" wire:click="clearFilters" class="text-xs text-brand-primary hover:text-brand-primary-dark">
                                        {{ __('frontend/brands.sidebar.clear') }}
                                    </button>
                        </div>
                            </div>
                        @endif

                        <div class="mt-6 pt-6 border-t border-sage/20">
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="$set('sortBy', 'featured')"
                                        class="filter-button-featured px-6 py-3 border-2 border-sage text-sage font-semibold text-sm transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group {{ $sortBy === 'featured' ? 'active' : '' }}">
                                    <div class="absolute inset-0 bg-sage opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <svg class="w-4 h-4 relative z-10 transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="relative z-10 transition-colors duration-300 group-hover:text-dark">{{ __('frontend/brands.sidebar.quick.featured') }}</span>
                                </button>
                                <button type="button" wire:click="$set('sortBy', 'products_count')"
                                        class="filter-button-products px-6 py-3 border-2 border-sage text-sage font-semibold text-sm transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group {{ $sortBy === 'products_count' ? 'active' : '' }}">
                                    <div class="absolute inset-0 bg-sage opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <svg class="w-4 h-4 relative z-10 transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M9 7h6m-3 0v14" />
                                    </svg>
                                    <span class="relative z-10 transition-colors duration-300 group-hover:text-dark">{{ __('frontend/brands.sidebar.quick.most_products') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Hero Section -->
                <section class="relative bg-dark text-sage overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-primary/10 rotate-45 transform translate-x-16 -translate-y-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-sage/10 rotate-45 transform -translate-x-12 translate-y-12"></div>
                    
                    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <!-- Left Column - Text Content -->
                            <div class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-brand-primary flex items-center justify-center group hover:scale-110 transition-transform duration-300">
                                        <svg class="w-6 h-6 text-white group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <h1 class="text-4xl lg:text-5xl font-bold text-sage leading-tight">
                                            {{ __('frontend/brands.hero.title') }}
                                        </h1>
                                        <p class="text-base text-ash max-w-xl leading-relaxed">
                                            {{ __('frontend/brands.hero.subtitle') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}" 
                                       class="browse-products-btn px-6 py-3 bg-sage text-dark font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-gradient-to-r from-brand-primary to-brand-primary-dark opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>
                                        <svg class="w-4 h-4 relative z-30 transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <span class="relative z-30 transition-colors duration-300 text-dark">{{ __('frontend/brands.hero.buttons.browse_products') }}</span>
                                    </a>
                                    <a href="#featured-brands" 
                                       class="featured-brands-btn px-6 py-3 border-2 border-sage text-sage font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-sage opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <svg class="w-4 h-4 relative z-10 transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                    </svg>
                                        <span class="relative z-10 transition-colors duration-300 group-hover:text-dark">{{ __('frontend/brands.hero.buttons.featured_brands') }}</span>
                                    </a>
                                                        </div>
                                                    </div>

                            <!-- Right Column - Stats -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Primary Stat Card -->
                                <div class="bg-brand-primary p-4 transition-all duration-300 relative overflow-hidden hover:bg-brand-primary-dark hover:shadow-xl hover:shadow-brand-primary/30 hover:scale-102">
                                    <div class="absolute top-0 right-0 w-12 h-12 bg-white/10 rotate-45 transform translate-x-6 -translate-y-6 transition-all duration-300 hover:bg-white/20"></div>
                                    <div class="space-y-2 relative z-10">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:bg-white/30">
                                                <svg class="w-2.5 h-2.5 text-white transition-all duration-300 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </div>
                                            <p class="text-2xl font-bold text-white transition-all duration-300 hover:text-brand-light">
                                                {{ number_format($totalBrands) }}
                                            </p>
                                        </div>
                                        <p class="text-brand-light font-semibold text-sm transition-all duration-300 hover:text-white">
                                            {{ __('frontend/brands.stats.total_brands') }}
                                        </p>
                                                    </div>
                                                </div>

                                <!-- Secondary Stat Card -->
                                <div class="border-2 border-brand-primary p-4 transition-all duration-300 relative overflow-hidden bg-dark/50 hover:bg-sage/10 hover:border-sage hover:shadow-xl hover:shadow-sage/20 hover:scale-102">
                                    <div class="absolute top-0 right-0 w-8 h-8 bg-brand-primary/10 rotate-45 transform translate-x-4 -translate-y-4 transition-all duration-300 hover:bg-sage/20"></div>
                                    <div class="space-y-2 relative z-10">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 bg-brand-primary/20 rounded-full flex items-center justify-center transition-all duration-300 hover:bg-sage/30">
                                                <svg class="w-2.5 h-2.5 text-brand-primary transition-all duration-300 hover:text-sage hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <p class="text-2xl font-bold text-sage transition-all duration-300 hover:text-dark">
                                                {{ number_format($totalProducts) }}
                                            </p>
                                </div>
                                        <p class="text-ash font-semibold text-sm transition-all duration-300 hover:text-sage">
                                            {{ __('frontend/brands.stats.products_available') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                                </div>
                </section>

                @if($paginator->count() > 0)
                    <!-- Featured Brands Section -->
                    @if($featuredBrands->count() > 0)
                        <section id="featured-brands">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <h2 class="text-2xl font-bold text-dark">{{ __('frontend/brands.sections.featured_brands') }}</h2>
                                <span class="text-sm text-gray-500">({{ $featuredBrands->count() }})</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                                @foreach($featuredBrands->take(6) as $brand)
                                    <div class="group bg-white text-dark overflow-hidden border border-ash/30 hover:border-ash/60 transition-all duration-300 relative shadow-sm hover:shadow-md">
                                         <!-- Featured Badge -->
                                         <div class="absolute top-4 right-4 z-10">
                                             <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-500/20 text-amber-300 text-xs font-semibold rounded-full border border-amber-500/30">
                                                 <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                     <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                 </svg>
                                                {{ __('frontend/brands.sections.featured_badge') }}
                                             </span>
                                         </div>
 
                                         <!-- Brand Logo Container -->
                                        <div class="relative overflow-hidden aspect-[4/3] bg-white flex items-center justify-center p-8">
                                            @if($brand->getFirstMediaUrl('logo'))
                                                <img
                                                    src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                    alt="{{ $brand->getTranslatedName() }}"
                                                    loading="lazy"
                                                    class="max-h-24 max-w-full object-contain transition-transform duration-500 group-hover:scale-110"
                                                />
                                            @else
                                                    <div class="text-center">
                                                    <div class="w-16 h-16 bg-ash/10 flex items-center justify-center mb-3 group-hover:bg-ash/20 transition-colors duration-300">
                                                        <span class="text-xl font-bold text-dark">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                    </div>
                                                    <div class="text-xs text-stone">{{ $brand->name }}</div>
                                                </div>
                                            @endif
                                            
                                            <!-- Hover overlay -->
                                            <div class="absolute inset-0 bg-sage/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        </div>

                                        <!-- Brand Content -->
                                        <div class="p-6 space-y-3">
                                            <h3 class="text-lg font-semibold text-dark group-hover:text-stone transition-colors duration-300">
                                                <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                                    {{ $brand->getTranslatedName() }}
                                                </a>
                                            </h3>

                                            @if($brand->getTranslatedDescription())
                                                <p class="text-sm text-stone line-clamp-2 leading-relaxed">
                                                    {{ $brand->getTranslatedDescription() }}
                                                </p>
                                            @endif

                                            <div class="flex items-center justify-between pt-2">
                                                <span class="inline-flex items-center px-3 py-1 bg-ash/10 text-dark text-xs font-medium border border-ash/30 group-hover:bg-ash/20 group-hover:border-ash/40 transition-all duration-300 text-left">
                                                    {{ $brand->products_count }} {{ trans_choice('frontend/brands.products', $brand->products_count, ['count' => $brand->products_count]) }}
                                                </span>
                                                <svg class="w-4 h-4 text-stone group-hover:text-dark transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <!-- All Brands Grid -->
                    <section>
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-bold text-dark">{{ __('frontend/brands.sections.all_brands') }}</h2>
                                <span class="text-sm text-gray-500">({{ $totalBrands }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">{{ __('frontend/brands.sections.view') }}:</span>
                                <div class="flex items-center bg-dark text-sage rounded-lg border border-ash/30 p-1">
                                    <button type="button" class="px-3 py-1 text-sm font-medium text-dark bg-sage rounded-md">
                                        {{ __('frontend/brands.sections.grid') }}
                                    </button>
                                    <button type="button" class="px-3 py-1 text-sm font-medium text-ash hover:text-sage">
                                        {{ __('frontend/brands.sections.list') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div wire:loading.flex class="min-h-[200px] items-center justify-center rounded-2xl border border-dashed border-ash/20 py-12 text-brand-primary">
                            <div class="inline-flex items-center gap-3 text-sm font-medium">
                                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('frontend/brands.sections.loading_brands') }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8" wire:loading.remove>
                            @foreach($paginator as $brand)
                                <div class="group bg-white text-dark overflow-hidden border border-ash/30 hover:border-ash/60 transition-all duration-300 shadow-sm hover:shadow-md">
                                    <!-- Brand Logo Container -->
                                    <div class="relative overflow-hidden aspect-[4/3] bg-white flex items-center justify-center p-8">
                                        @if($brand->getFirstMediaUrl('logo'))
                                            <img
                                                src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                alt="{{ $brand->getTranslatedName() }}"
                                                loading="lazy"
                                                class="max-h-24 max-w-full object-contain transition-transform duration-500 group-hover:scale-110"
                                            />
                                        @else
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-ash/10 flex items-center justify-center mb-3 group-hover:bg-ash/20 transition-colors duration-300">
                                                    <span class="text-xl font-bold text-dark">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                                </div>
                                                <div class="text-xs text-stone">{{ $brand->name }}</div>
                                            </div>
                                        @endif
                                        
                                        <!-- Hover overlay -->
                                        <div class="absolute inset-0 bg-sage/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>

                                    <!-- Brand Content -->
                                    <div class="p-6 space-y-3">
                                        <h3 class="text-lg font-semibold text-dark group-hover:text-stone transition-colors duration-300">
                                            <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->getTranslatedSlug()]) }}" class="stretched-link">
                                                {{ $brand->getTranslatedName() }}
                                            </a>
                                        </h3>

                                        @if($brand->getTranslatedDescription())
                                            <p class="text-sm text-stone line-clamp-2 leading-relaxed">
                                                {{ $brand->getTranslatedDescription() }}
                                            </p>
                                        @endif

                                        <div class="flex items-center justify-between pt-2">
                                            <span class="inline-flex items-center px-3 py-1 bg-ash/10 text-dark text-xs font-medium border border-ash/30 group-hover:bg-ash/20 group-hover:border-ash/40 transition-all duration-300 text-left">
                                                {{ $brand->products_count }} {{ trans_choice('frontend/brands.products', $brand->products_count, ['count' => $brand->products_count]) }}
                                            </span>
                                            <svg class="w-4 h-4 text-stone group-hover:text-dark transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($paginator->hasPages())
                            <div class="mt-12 border-t border-ash/20 pt-6">
                                    <x-shared.pagination :paginator="$paginator" />
                                </div>
                            @endif
                        </section>
                    @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-dark mb-2">{{ __('frontend/brands.empty.title') }}</h3>
                        <p class="text-gray-600 mb-6">
                            @if(!empty($this->search))
                                {{ __('frontend/brands.empty.search_hint') }}
                            @else
                                {{ __('frontend/brands.empty.no_data') }}
                            @endif
                        </p>
                        @if(!empty($this->search))
                            <button type="button" wire:click="$set('search', '')" class="inline-flex items-center px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-dark transition-colors">
                                {{ __('frontend/brands.empty.clear_search') }}
                            </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="bg-dark text-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h2 class="text-3xl font-bold mb-4">{{ __('frontend/brands.cta.title') }}</h2>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    {{ __('frontend/brands.cta.subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:support@statybae.com" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-dark bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('frontend/brands.cta.contact_us') }}
                    </a>
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('frontend/brands.cta.browse_products') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <x-filament-actions::modals />
</main>