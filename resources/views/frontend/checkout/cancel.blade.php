<x-layouts.base title="{{ __('ui.checkout_cancelled') }}">
    <div class="max-w-3xl mx-auto px-4 py-16 text-center space-y-6">
        <div class="mx-auto w-16 h-16 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('ui.checkout_was_cancelled') }}</h1>
        <p class="text-gray-600 dark:text-gray-300">{{ __('ui.your_payment_was_not_completed_you_can_return_to_the_cart_to_try_again') }}</p>
        <div class="space-x-4">
            <a href="{{ route('frontend.cart.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('ui.return_to_cart') }}</a>
            <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">{{ __('ui.continue_browsing') }}</a>
        </div>
    </div>
</x-layouts.base>
