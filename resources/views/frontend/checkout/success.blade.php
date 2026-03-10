<x-layouts.base title="{{ __('ui.order_confirmed') }}">
    <div class="max-w-4xl mx-auto px-4 py-16 space-y-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('ui.thank_you_for_your_order') }}</h1>
        <p class="text-gray-600 dark:text-gray-300">{{ __('ui.your_order_number_is_number', ['number' => $order->number]) }}</p>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6 text-left">
            <h2 class="text-xl font-semibold mb-4">{{ __('ui.order_summary') }}</h2>
            <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex justify-between">
                    <dt>{{ __('messages.subtotal') }}</dt>
                    <dd>{{ app_money_format($order->subtotal) }}</dd>
                </div>
                @vatenabled
                <div class="flex justify-between">
                    <dt>{{ __('ui.tax') }}</dt>
                    <dd>{{ app_money_format($order->tax_amount) }}</dd>
                </div>
                @endvatenabled
                <div class="flex justify-between">
                    <dt>{{ __('messages.shipping') }}</dt>
                    <dd>{{ app_money_format($order->shipping_amount) }}</dd>
                </div>
                <div class="flex justify-between text-primary-700">
                    <dt>{{ __('messages.discount') }}</dt>
                    <dd>-{{ app_money_format($order->discount_amount) }}</dd>
                </div>
                <div class="flex justify-between text-lg font-semibold text-primary-700">
                    <dt>{{ __('messages.total') }}</dt>
                    <dd>{{ app_money_format($order->total) }}</dd>
                </div>
            </dl>
        </section>

        <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('ui.continue_shopping') }}</a>
    </div>
</x-layouts.base>
