<x-layouts.base title="{{ __('messages.frontend') }}">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-6">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('frontend.discounts.active_title') }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($discounts as $discount)
                <article class="p-4 border border-dashed border-primary-200 dark:border-primary-800 rounded-xl bg-primary-50 dark:bg-primary-900/10">
                    <h2 class="text-xl font-semibold text-primary-900 dark:text-primary-200">{{ $discount->name }}</h2>
                    <p class="mt-2 text-sm text-primary-700 dark:text-primary-300">{{ $discount->description }}</p>
                    <p class="mt-4 text-sm text-primary-600">{{ __('messages.frontend', ['value' => $discount->value]) }}</p>
                </article>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.frontend') }}</p>
            @endforelse
        </div>
        <div>
            {{ $discounts->links() }}
        </div>
        <div class="mt-8">
            <a href="{{ route('frontend.discounts.coupons') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('frontend.discounts.view_coupons') }}</a>
        </div>
    </div>
</x-layouts.base>
