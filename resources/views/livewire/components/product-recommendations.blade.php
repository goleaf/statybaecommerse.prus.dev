<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Product Recommendations') }}
            </h2>
        </div>

        @if($this->recommendations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->recommendations as $product)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="aspect-w-16 aspect-h-9 mb-4">
                            @if($product->getFirstMediaUrl('default'))
                                <img src="{{ $product->getFirstMediaUrl('default') }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-48 object-cover rounded-md">
                            @else
                                <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('No Image') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <h3 class="font-medium text-gray-900 dark:text-white mb-2">
                            {{ $product->name }}
                        </h3>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ Str::limit($product->description, 100) }}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($product->price, 2) }} €
                            </span>
                            
                            <button wire:click="addToCart({{ $product->id }})" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                {{ __('Add to Cart') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    {{ __('No Recommendations Available') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('We don\'t have any product recommendations for you at the moment.') }}
                </p>
            </div>
        @endif
    </div>
</div>
