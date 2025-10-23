<x-layouts.base title="{{ __('Coupons') }}">
    <div class="max-w-4xl mx-auto px-4 py-10 space-y-6">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Available coupons') }}</h1>
        <div class="space-y-4">
            @forelse ($coupons as $coupon)
                <article class="p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $coupon->name ?? $coupon->code }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $coupon->description }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-sm">{{ $coupon->code }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Value: :value', ['value' => $coupon->value]) }}</p>
                </article>
            @empty
                <p class="text-gray-500 dark:text-gray-400">{{ __('No coupons are available at this time.') }}</p>
            @endforelse
        </div>
        <div>
            {{ $coupons->links() }}
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.copy-coupon').forEach((button) => {
                button.addEventListener('click', () => {
                    navigator.clipboard.writeText(button.dataset.code || '');
                    button.classList.add('bg-blue-600', 'text-white');
                    window.setTimeout(() => {
                        button.classList.remove('bg-blue-600', 'text-white');
                    }, 1200);
                });
            });
        });
    </script>
@endsection
