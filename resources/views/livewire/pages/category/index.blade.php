<div>
    <x-container class="py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Categories') }}</h1>
        
        @if($categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" 
                       class="group bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            @if($category->getFirstMediaUrl('images'))
                                <img src="{{ $category->getFirstMediaUrl('images', 'medium') }}"
                                     alt="{{ $category->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <span class="text-white text-2xl font-bold">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ $category->name }}
                            </h3>
                            @if($category->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $category->products_count ?? $category->products()->count() }} {{ __('products') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📂</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No categories available') }}</h3>
                <p class="text-gray-500">{{ __('Categories will appear here once they are added') }}</p>
            </div>
        @endif
    </x-container>
</div>