<div class="space-y-6">
    <header class="rounded border border-gray-200 bg-gray-50 p-5">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.orders') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.navigation.orders_description') }}</p>
    </header>
    @if ($orders->isEmpty())
        <div class="rounded border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">{{ __('frontend.account.orders.empty_message') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded border border-gray-200 bg-gray-50">
            <div class="divide-y divide-gray-200">
                @foreach ($orders as $order)
                    <x-order :order="$order" />
                @endforeach
            </div>
        </div>

        <div class="rounded border border-gray-200 bg-gray-50 p-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
