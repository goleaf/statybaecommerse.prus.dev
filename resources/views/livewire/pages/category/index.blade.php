<div>
    <x-container class="py-8">
        <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
            <ol class="list-reset flex">
                <li><a href="{{ route('home') }}" class="hover:text-gray-700">{{ __('Home') }}</a></li>
                <li class="mx-2">/</li>
                <li aria-current="page" class="text-gray-700 font-medium">{{ __('Categories') }}</li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Categories') }}</h1>
                <p class="text-gray-600">{{ __('Explore our comprehensive range of categories') }}</p>
            </div>

            <div class="w-full md:w-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label for="search" class="sr-only">{{ __('Search') }}</label>
                    <input id="search" type="search" wire:model.debounce.400ms="search" placeholder="{{ __('Search categories...') }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="brand" class="sr-only">{{ __('Brand') }}</label>
                    <select id="brand" wire:model="brandId" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <input type="number" min="0" step="0.01" wire:model="priceMin" placeholder="{{ __('Min price') }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    <input type="number" min="0" step="0.01" wire:model="priceMax" placeholder="{{ __('Max price') }}" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-center gap-2">
                    <input id="hasProducts" type="checkbox" wire:model="hasProducts" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <label for="hasProducts" class="text-sm text-gray-700">{{ __('Only categories with products') }}</label>
                </div>
                <div>
                    <label for="sort" class="sr-only">{{ __('Sort by') }}</label>
                    <select id="sort" wire:model="sort" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="name_asc">{{ __('Name (A–Z)') }}</option>
                        <option value="name_desc">{{ __('Name (Z–A)') }}</option>
                        <option value="products_desc">{{ __('Most products') }}</option>
                        <option value="products_asc">{{ __('Fewest products') }}</option>
                    </select>
                </div>
            </div>
        </div>

        @if($categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" 
                       class="group bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-shadow duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                                {{ $category->products_count }} {{ __('translations.products') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $categories->links() }}
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
