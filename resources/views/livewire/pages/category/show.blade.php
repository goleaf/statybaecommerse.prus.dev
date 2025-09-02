<div>
    <x-container class="py-8">
        <!-- Category Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                @if($category->getFirstMediaUrl('images'))
                    <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}"
                         alt="{{ $category->name }}"
                         class="w-16 h-16 object-cover rounded-lg">
                @endif
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="text-gray-600 mt-2">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Sort Options -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-sm text-gray-600">
                {{ $products->total() }} {{ __('products') }}
            </p>
            <select wire:model.live="sortBy" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="created_at">{{ __('Newest') }}</option>
                <option value="name">{{ __('Name') }}</option>
                <option value="price">{{ __('Price: Low to High') }}</option>
            </select>
        </div>
        
        <!-- Products Grid -->
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
            
            {{ $products->links() }}
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📦</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No products in this category yet.') }}</h3>
                <p class="text-gray-500">{{ __('Check back later for new products') }}</p>
            </div>
        @endif
    </x-container>
</div>