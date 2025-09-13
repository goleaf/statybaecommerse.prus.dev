@extends('layouts.app')

@section('title', $collection->meta_title ?: $collection->name)
@section('description', $collection->meta_description ?: Str::limit(strip_tags($collection->description), 160))
@section('keywords', $collection->meta_keywords)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Collection Header -->
    <div class="mb-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">
                        {{ __('common.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('collections.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">
                            {{ __('collections.title') }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">{{ $collection->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Collection Banner/Image -->
        @if($collection->banner)
            <div class="mb-6">
                <img src="{{ $collection->getBannerUrl('lg') }}" 
                     alt="{{ $collection->name }}"
                     class="w-full h-64 md:h-80 object-cover rounded-lg">
            </div>
        @endif

        <!-- Collection Info -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $collection->name }}</h1>
            @if($collection->description)
                <div class="text-lg text-gray-600 max-w-3xl mx-auto">
                    {!! $collection->description !!}
                </div>
            @endif
            <div class="mt-4 flex items-center justify-center space-x-6 text-sm text-gray-500">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    {{ $collection->products_count }} {{ __('collections.products') }}
                </span>
                @if($collection->is_automatic)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ __('collections.automatic') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="mb-6">
        <form id="collection-filters" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 flex gap-2">
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('collections.filters.all_categories') }}</option>
                    @foreach($collection->products->pluck('categories')->flatten()->unique('id') as $category)
                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                
                <input type="number" 
                       name="price_min" 
                       placeholder="{{ __('collections.filters.min_price') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                
                <input type="number" 
                       name="price_max" 
                       placeholder="{{ __('collections.filters.max_price') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="flex gap-2">
                <select name="sort" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('collections.filters.sort_default') }}</option>
                    <option value="price_asc">{{ __('collections.filters.sort_price_asc') }}</option>
                    <option value="price_desc">{{ __('collections.filters.sort_price_desc') }}</option>
                    <option value="name_asc">{{ __('collections.filters.sort_name_asc') }}</option>
                    <option value="name_desc">{{ __('collections.filters.sort_name_desc') }}</option>
                    <option value="newest">{{ __('collections.filters.sort_newest') }}</option>
                    <option value="oldest">{{ __('collections.filters.sort_oldest') }}</option>
                </select>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('collections.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div id="products-container">
        @include('collections.partials.products', ['products' => $products])
    </div>

    <!-- Related Collections -->
    @if($relatedCollections->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('collections.related_collections') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedCollections as $relatedCollection)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        @if($relatedCollection->image)
                            <img src="{{ $relatedCollection->getImageUrl('md') }}" 
                                 alt="{{ $relatedCollection->name }}"
                                 class="w-full h-32 object-cover">
                        @endif
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $relatedCollection->name }}</h3>
                            <p class="text-sm text-gray-500 mb-3">{{ $relatedCollection->products_count }} {{ __('collections.products') }}</p>
                            <a href="{{ route('collections.show', $relatedCollection) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('collections.view_collection') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('collection-filters').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    fetch(`{{ route('collections.products', $collection) }}?${params}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('products-container').innerHTML = data.html;
        })
        .catch(error => console.error('Error:', error));
});
</script>
@endpush
@endsection
