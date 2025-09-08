<x-container class="py-10 sm:py-14 lg:py-16">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center">
            <svg class="size-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m6.75 4.5v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3.375m6-9V2.625c0-.621-.504-1.125-1.125-1.125H15.75c-.621 0-1.125.504-1.125 1.125v3.375c0 .621.504 1.125 1.125 1.125h1.5c.621 0 1.125-.504 1.125-1.125V6.375a1.125 1.125 0 011.125-1.125H21a.75.75 0 010 1.5h-.375a.375.375 0 00-.375.375v3.375c0 .621-.504 1.125-1.125 1.125h-1.5a1.125 1.125 0 01-1.125-1.125V7.875c0-.621-.504-1.125-1.125-1.125H12a.75.75 0 000 1.5h.375c.621 0 1.125.504 1.125 1.125v3.375c0 .621-.504 1.125-1.125 1.125h-1.5Z" />
            </svg>
            <div class="ml-4">
                <h4 class="font-heading font-medium text-gray-900">{{ __('Free shipping') }}</h4>
                <p class="text-sm leading-5 text-gray-500">
                    {{ __('From :amount', ['amount' => \Illuminate\Support\Number::currency(5000, current_currency(), app()->getLocale())]) }}
                </p>
            </div>
        </div>
        <div class="flex items-center">
            <svg class="size-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
            </svg>
            <div class="ml-4">
                <h4 class="font-heading font-medium text-gray-900">{{ __('24/7 customer support') }}</h4>
                <p class="text-sm leading-5 text-gray-500">{{ __('Friendly 24/7 customer support') }}</p>
            </div>
        </div>
        <div class="flex items-center">
            <svg class="size-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <div class="ml-4">
                <h4 class="font-heading font-medium text-gray-900">{{ __('Secure payment') }}</h4>
                <p class="text-sm leading-5 text-gray-500">{{ __('On all orders') }}</p>
            </div>
        </div>
        <div class="flex items-center">
            <svg class="size-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <div class="ml-4">
                <h4 class="font-heading font-medium text-gray-900">{{ __('Return when you\'re ready') }}</h4>
                <p class="text-sm leading-5 text-gray-500">{{ __('60 days of free returns') }}</p>
            </div>
        </div>
    </div>
</x-container>
