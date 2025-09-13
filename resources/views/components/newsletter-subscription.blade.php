@props([
    'title' => null,
    'subtitle' => null,
    'showPrivacy' => true,
    'variant' => 'default', // default, compact, hero
])

@php
    $title = $title ?? __('newsletter_title');
    $subtitle = $subtitle ?? __('newsletter_subtitle');
    $isHero = $variant === 'hero';
    $isCompact = $variant === 'compact';
@endphp

<div class="newsletter-subscription {{ $isHero ? 'bg-gradient-to-br from-blue-600 via-blue-700 to-purple-600' : 'bg-gray-50' }} {{ $isCompact ? 'py-8' : 'py-16 lg:py-24' }}"
     x-data="newsletterSubscription()">

    @if ($isHero)
        {{-- Animated background elements for hero variant --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-float"
                 style="animation-delay: 2s;"></div>
        </div>
    @endif

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center {{ $isCompact ? 'max-w-2xl mx-auto' : 'max-w-4xl mx-auto' }}">
            {{-- Title --}}
            <h2
                class="{{ $isHero ? 'text-4xl lg:text-5xl font-bold text-white mb-6' : 'text-3xl lg:text-4xl font-bold text-gray-900 mb-4' }} {{ $isCompact ? 'text-2xl lg:text-3xl' : '' }}">
                {{ $title }}
            </h2>

            {{-- Subtitle --}}
            <p
               class="{{ $isHero ? 'text-xl text-white/90 mb-12' : 'text-lg text-gray-600 mb-8' }} {{ $isCompact ? 'text-base mb-6' : '' }} max-w-3xl mx-auto text-pretty">
                {{ $subtitle }}
            </p>

            {{-- Subscription Form --}}
            <form @submit.prevent="subscribe"
                  class="{{ $isCompact ? 'max-w-md mx-auto flex flex-col sm:flex-row gap-3' : 'max-w-lg mx-auto flex flex-col sm:flex-row gap-4' }} {{ $isHero ? 'animate-fade-in-up' : '' }}"
                  style="{{ $isHero ? 'animation-delay: 0.3s;' : '' }}">

                <div class="flex-1 relative">
                    <input
                           type="email"
                           x-model="email"
                           placeholder="{{ __('email_placeholder') }}"
                           class="w-full {{ $isHero ? 'px-6 py-4 rounded-2xl border-0 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 text-gray-900 placeholder:text-gray-500 shadow-large' : 'px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500' }} {{ $isCompact ? 'px-3 py-2 text-sm' : '' }}"
                           required
                           :disabled="loading">

                    {{-- Email Icon --}}
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>

                <button
                        type="submit"
                        :disabled="loading || !email"
                        class="{{ $isHero ? 'bg-white text-blue-700 px-8 py-4 rounded-2xl font-semibold hover:bg-gray-50 transition-all duration-300 shadow-large hover:shadow-glow-lg transform hover:scale-105' : 'btn-gradient px-6 py-3 rounded-xl font-semibold' }} {{ $isCompact ? 'px-4 py-2 text-sm' : '' }} disabled:opacity-50 disabled:cursor-not-allowed">

                    <span x-show="!loading">{{ __('subscribe') }}</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        {{ __('Subscribing...') }}
                    </span>

                    @if (!$isCompact)
                        <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    @endif
                </button>
            </form>

            {{-- Privacy Notice --}}
            @if ($showPrivacy)
                <p
                   class="{{ $isHero ? 'text-sm text-white/70 mt-6' : 'text-sm text-gray-500 mt-4' }} {{ $isCompact ? 'text-xs mt-3' : '' }}">
                    {{ __('privacy_unsubscribe_notice') }}
                </p>
            @endif

            {{-- Success/Error Messages --}}
            <div x-show="message"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mt-4 p-4 rounded-xl"
                 :class="messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' :
                     'bg-red-50 text-red-800 border border-red-200'"
                 style="display: none;">
                <div class="flex items-center gap-2">
                    <svg x-show="messageType === 'success'" class="w-5 h-5 text-green-600" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="messageType === 'error'" class="w-5 h-5 text-red-600" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    <span x-text="message"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function newsletterSubscription() {
        return {
            email: '',
            loading: false,
            message: '',
            messageType: 'success',

            async subscribe() {
                if (!this.email || this.loading) return;

                this.loading = true;
                this.message = '';

                try {
                    const response = await fetch('/newsletter/subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            email: this.email
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.messageType = 'success';
                        this.message = data.message || '{{ __('Successfully subscribed to newsletter!') }}';
                        this.email = '';
                    } else {
                        this.messageType = 'error';
                        this.message = data.message || '{{ __('Something went wrong. Please try again.') }}';
                    }
                } catch (error) {
                    this.messageType = 'error';
                    this.message = '{{ __('Network error. Please check your connection and try again.') }}';
                } finally {
                    this.loading = false;

                    // Clear message after 5 seconds
                    setTimeout(() => {
                        this.message = '';
                    }, 5000);
                }
            }
        }
    }
</script>

