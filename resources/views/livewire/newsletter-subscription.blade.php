<div class="w-full max-w-md">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-sage/20 border border-sage/40 text-sage rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-4 p-4 bg-sage/20 border border-sage/40 text-sage rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    @if (!$showSuccess)
        <form wire:submit.prevent="subscribe" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-ash mb-2">
                    {{ __('newsletter.email_address') }} <span class="text-red-400">*</span>
                </label>
                <input 
                    type="email" 
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-3 bg-white/10 border border-ash/30 rounded-lg focus:ring-2 focus:ring-sage focus:border-sage text-sage placeholder:text-ash/50 transition-colors duration-200"
                    placeholder="{{ __('newsletter.email_placeholder') }}"
                    required
                >
                @error('email') 
                    <p class="mt-1 text-sm text-red-300">{{ $message }}</p> 
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-ash mb-2">
                        {{ __('newsletter.first_name') }}
                    </label>
                    <input 
                        type="text" 
                        id="first_name"
                        wire:model="first_name"
                        class="w-full px-4 py-3 bg-white/10 border border-ash/30 rounded-lg focus:ring-2 focus:ring-sage focus:border-sage text-sage placeholder:text-ash/50 transition-colors duration-200"
                        placeholder="{{ __('newsletter.first_name_placeholder') }}"
                    >
                    @error('first_name') 
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-ash mb-2">
                        {{ __('newsletter.last_name') }}
                    </label>
                    <input 
                        type="text" 
                        id="last_name"
                        wire:model="last_name"
                        class="w-full px-4 py-3 bg-white/10 border border-ash/30 rounded-lg focus:ring-2 focus:ring-sage focus:border-sage text-sage placeholder:text-ash/50 transition-colors duration-200"
                        placeholder="{{ __('newsletter.last_name_placeholder') }}"
                    >
                    @error('last_name') 
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-ash mb-2">
                    {{ __('newsletter.company') }}
                </label>
                <input 
                    type="text" 
                    id="company"
                    wire:model="company"
                    class="w-full px-4 py-3 bg-white/10 border border-ash/30 rounded-lg focus:ring-2 focus:ring-sage focus:border-sage text-sage placeholder:text-ash/50 transition-colors duration-200"
                    placeholder="{{ __('newsletter.company_placeholder') }}"
                >
                @error('company') 
                    <p class="mt-1 text-sm text-red-300">{{ $message }}</p> 
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-ash mb-2">
                    {{ __('newsletter.interests') }}
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        'products' => __('newsletter.interests_products'),
                        'news' => __('newsletter.interests_news'),
                        'promotions' => __('newsletter.interests_promotions'),
                        'events' => __('newsletter.interests_events'),
                        'blog' => __('newsletter.interests_blog'),
                        'technical' => __('newsletter.interests_technical'),
                    ] as $value => $label)
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model="interests"
                                value="{{ $value }}"
                                class="rounded border-ash/30 bg-white/10 text-sage focus:ring-sage"
                            >
                            <span class="text-sm text-ash">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('interests') 
                    <p class="mt-1 text-sm text-red-300">{{ $message }}</p> 
                @enderror
            </div>

            <button 
                type="submit"
                class="w-full bg-sage text-dark py-3 px-6 rounded-lg font-semibold hover:bg-sage/90 focus:ring-2 focus:ring-sage focus:ring-offset-2 focus:ring-offset-dark transition-all duration-200 transform hover:scale-105"
                wire:loading.attr="disabled"
                wire:target="subscribe"
            >
                <span wire:loading.remove wire:target="subscribe">
                    {{ __('newsletter.subscribe_button') }}
                </span>
                <span wire:loading wire:target="subscribe" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('newsletter.subscribing') }}
                </span>
            </button>

            <p class="text-xs text-ash/70 text-center">
                {{ __('newsletter.privacy_notice') }}
            </p>
        </form>
    @else
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-sage/20 mb-4">
                <svg class="h-8 w-8 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-sage mb-2">
                {{ __('newsletter.subscription_success_title') }}
            </h3>
            <p class="text-ash mb-4">
                {{ __('newsletter.subscription_success_message') }}
            </p>
            <button 
                wire:click="resetForm"
                wire:confirm="{{ __('translations.confirm_reset_newsletter_form') }}"
                class="text-sage hover:text-sage/80 font-medium text-sm"
            >
                {{ __('newsletter.subscribe_another') }}
            </button>
        </div>
    @endif
</div>

