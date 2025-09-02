<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Statybos įrankiai ir medžiagos</h1>
            <p class="mt-2 text-gray-600">Profesionalūs sprendimai statybininkams Lietuvoje</p>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Ieškoti produktų..."
                    class="w-full px-4 py-3 pl-10 pr-4 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Filter Toggle -->
            <div class="flex justify-between items-center">
                <button 
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtrai
                </button>
                
                <div class="text-sm text-gray-600">
                    Rasta {{ $products->total() }} produktų
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Filters Sidebar -->
            @if($showFilters)
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <h3 class="text-lg font-semibold mb-4">Filtrai</h3>
                    
                    <!-- Categories Filter -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Kategorijos</h4>
                        <div class="space-y-2">
                            @foreach($availableCategories as $category)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="categories" 
                                        value="{{ $category->id }}"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Brands Filter -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Gamintojai</h4>
                        <div class="space-y-2">
                            @foreach($availableBrands as $brand)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="brands" 
                                        value="{{ $brand->id }}"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">{{ $brand->name }} ({{ $brand->products_count }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Kaina (€)</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <input 
                                type="number" 
                                wire:model.live.debounce.500ms="minPrice" 
                                placeholder="Nuo"
                                min="0"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                            >
                            <input 
                                type="number" 
                                wire:model.live.debounce.500ms="maxPrice" 
                                placeholder="Iki"
                                min="0"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                            >
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <button 
                        wire:click="clearFilters"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                    >
                        Išvalyti filtrus
                    </button>
                </div>
            </div>
            @endif

            <!-- Products Grid -->
            <div class="{{ $showFilters ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                <!-- Sort Options -->
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-2">
                        <button 
                            wire:click="sortBy('name')"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 {{ $sortBy === 'name' ? 'bg-blue-50 border-blue-300' : '' }}"
                        >
                            Pavadinimas
                            @if($sortBy === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                        <button 
                            wire:click="sortBy('price')"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 {{ $sortBy === 'price' ? 'bg-blue-50 border-blue-300' : '' }}"
                        >
                            Kaina
                            @if($sortBy === 'price')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                        <button 
                            wire:click="sortBy('created_at')"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 {{ $sortBy === 'created_at' ? 'bg-blue-50 border-blue-300' : '' }}"
                        >
                            Naujausi
                            @if($sortBy === 'created_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <livewire:components.product-card :product="$product" :key="$product->id" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>

                <!-- No Results -->
                @if($products->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Produktų nerasta</h3>
                        <p class="mt-1 text-sm text-gray-500">Pabandykite pakeisti paieškos kriterijus arba filtrus.</p>
                        <div class="mt-6">
                            <button 
                                wire:click="clearFilters"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                Išvalyti filtrus
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
