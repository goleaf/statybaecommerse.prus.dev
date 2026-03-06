<div class="group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-gray-200 overflow-hidden">
    {{-- Product Image --}}
    <div class="relative aspect-square overflow-hidden bg-gray-50">
        @php
            $imageUrl = $product->getImageUrl('preview') ?? $product->main_image ?? $product->thumbnail ?? asset('images/placeholder-product.jpg');
            $productUrl = route('product.show', $product->slug ?? $product);
        @endphp
        <a href="{{ $productUrl }}" class="block h-full w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            >
        </a>

        {{-- Product Badges --}}
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            @if($product->is_featured)
                <span class="bg-yellow-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                    {{ __('translations.featured') }}
                </span>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            @if($showQuickView)
                <button 
                    wire:click="quickView"
                    class="w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-gray-50 transition-colors duration-200 text-gray-400 hover:text-blue-500"
                    title="{{ __('translations.quick_view') }}"
                >
                    <x-heroicon-o-eye class="w-5 h-5" />
                </button>
            @endif
        </div>

        {{-- Quick Add to Cart (appears on hover) --}}
        <div class="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button 
                wire:click="addToCart"
                class="w-full cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
            >
                {{ __('translations.add_to_cart') }}
            </button>
        </div>
    </div>

    {{-- Product Info --}}
    <div class="p-4">
        {{-- Brand --}}
        @if($product->brand)
            <p class="text-sm text-gray-500 mb-1">{{ $product->brand->name }}</p>
        @endif

        {{-- Product Name --}}
        <h3 class="font-medium text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
            <button wire:click="viewProduct" class="text-left w-full">
                {{ $product->name }}
            </button>
        </h3>

        {{-- Price --}}
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg font-bold text-gray-900">
                {{ app_money_format($product->price) }}
            </span>
        </div>

        {{-- Stock Status --}}
        <div class="flex items-center justify-between">
            @if($product->track_inventory)
                @if($product->stock_quantity > 0)
                    <span class="text-sm text-green-600 flex items-center gap-1">
                        <x-heroicon-o-check-circle class="w-4 h-4" />
                        {{ __('translations.in_stock') }}
                        @if($product->stock_quantity <= $product->low_stock_threshold)
                            ({{ $product->stock_quantity }} {{ __('translations.left') }})
                        @endif
                    </span>
                @else
                    <span class="text-sm text-red-600 flex items-center gap-1">
                        <x-heroicon-o-x-circle class="w-4 h-4" />
                        {{ __('translations.out_of_stock') }}
                    </span>
                @endif
            @else
                <span class="text-sm text-green-600 flex items-center gap-1">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    {{ __('translations.available') }}
                </span>
            @endif

            {{-- View Product Link --}}
            <button 
                wire:click="viewProduct"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200"
            >
                {{ __('translations.view_details') }}
            </button>
        </div>
    </div>
</div>
