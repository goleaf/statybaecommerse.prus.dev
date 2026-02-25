<div class="grid grid-cols-[12fr_6fr_18fr] gap-3 py-1 w-full text-sm items-center">
    <!-- Col 1: Name -->
    <div class="flex flex-col justify-center pr-2">
        <span class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</span>
    </div>

    <!-- Col 2: SKU -->
    <div class="flex flex-col justify-center text-gray-600 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 pl-3">
        <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('messages.sku') }}:</span>
        <span class="font-mono text-xs">{{ $product->sku ?: '-' }}</span>
    </div>

    <!-- Col 3: Details as a mini table -->
    <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-600 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 pl-3 items-center">
        @if((float) $product->weight > 0) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.weight') }}:</span> <span>{{ (float) $product->weight }} kg</span></div> @endif
        @if((float) $product->length > 0) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.length') }}:</span> <span>{{ (float) $product->length }} cm</span></div> @endif
        @if((float) $product->width > 0) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.width') }}:</span> <span>{{ (float) $product->width }} cm</span></div> @endif
        @if((float) $product->height > 0) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.height') }}:</span> <span>{{ (float) $product->height }} cm</span></div> @endif
        @if($product->size) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.size') }}:</span> <span>{{ $product->size }} {{ $product->size_type }}</span></div> @endif
        @if($product->color) <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.color') }}:</span> <span>{{ $product->color }}</span></div> @endif
        @if($product->pack) 
            <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.pack') }}:</span> <span>{{ (float) $product->pack }} {{ $product->pack_type }}</span></div> 
        @elseif($product->pack_size) 
            <div class="flex flex-col"><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('translations.pack') }}:</span> <span>{{ (float) $product->pack_size }} {{ $product->pack_size_type }}</span></div> 
        @endif
    </div>
</div>

