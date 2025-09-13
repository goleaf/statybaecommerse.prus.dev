<div class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600 overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-2 -right-2 w-16 h-16 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute -bottom-2 -left-2 w-12 h-12 bg-white/10 rounded-full blur-xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    
    <div class="relative h-10 flex items-center justify-center">
        <div class="flex items-center gap-3 text-white">
            <!-- Animated icon -->
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            
            <!-- Banner text with modern styling -->
            <p class="text-sm font-semibold text-white/95 sm:px-6 lg:px-8 animate-fade-in">
                {{ __('Free shipping from :amount', ['amount' => format_money((float) config('starterkit.free_shipping_amount', 0), current_currency())]) }}
            </p>
            
            <!-- Optional close button -->
            <button class="flex-shrink-0 ml-2 text-white/80 hover:text-white transition-colors duration-200" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
