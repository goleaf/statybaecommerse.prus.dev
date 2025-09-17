<div class="w-full max-w-md mx-auto">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    @if (!$showSuccess)
        <form wire:submit.prevent="subscribe" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                    {{ __('newsletter.email_address') }} <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900 placeholder:text-slate-500 transition-colors duration-200"
                    placeholder="{{ __('newsletter.email_placeholder') }}"
                    required
                >
                @error('email') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-slate-700 mb-2">
                        {{ __('newsletter.first_name') }}
                    </label>
                    <input 
                        type="text" 
                        id="first_name"
                        wire:model="first_name"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900 placeholder:text-slate-500 transition-colors duration-200"
                        placeholder="{{ __('newsletter.first_name_placeholder') }}"
                    >
                    @error('first_name') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-slate-700 mb-2">
                        {{ __('newsletter.last_name') }}
                    </label>
                    <input 
                        type="text" 
                        id="last_name"
                        wire:model="last_name"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900 placeholder:text-slate-500 transition-colors duration-200"
                        placeholder="{{ __('newsletter.last_name_placeholder') }}"
                    >
                    @error('last_name') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-slate-700 mb-2">
                    {{ __('newsletter.company') }}
                </label>
                <input 
                    type="text" 
                    id="company"
                    wire:model="company"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900 placeholder:text-slate-500 transition-colors duration-200"
                    placeholder="{{ __('newsletter.company_placeholder') }}"
                >
                @error('company') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
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
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('interests') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105"
                wire:loading.attr="disabled"
                wire:target="subscribe"
            >
                <span wire:loading.remove wire:target="subscribe">
                    {{ __('newsletter.subscribe_button') }}
                </span>
                <span wire:loading wire:target="subscribe" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('newsletter.subscribing') }}
                </span>
            </button>

            <p class="text-xs text-slate-500 text-center">
                {{ __('newsletter.privacy_notice') }}
            </p>
        </form>
    @else
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 mb-2">
                {{ __('newsletter.subscription_success_title') }}
            </h3>
            <p class="text-slate-600 mb-4">
                {{ __('newsletter.subscription_success_message') }}
            </p>
            <button 
                wire:click="resetForm"
                wire:confirm="{{ __('translations.confirm_reset_newsletter_form') }}"
                class="text-blue-600 hover:text-blue-700 font-medium text-sm"
            >
                {{ __('newsletter.subscribe_another') }}
            </button>
        </div>
    @endif
</div>
