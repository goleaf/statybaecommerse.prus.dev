<div class="p-8 text-gray-900">
    <h1 class="text-2xl font-semibold">{{ __('Packing slip') }}</h1>

    <div class="mt-6 grid grid-cols-2 gap-6">
        <div>
            <h2 class="font-medium">{{ __('Order') }} #{{ $order->number }}</h2>
            <p class="text-sm text-gray-600">{{ __('Placed at') }}:
                {{ optional($order->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
            <p class="text-sm text-gray-600 mt-1">
                {{ __('Status') }}: <strong>{{ $order->status }}</strong> ·
                {{ __('Payment') }}: <strong>{{ $order->payment_status ?? __('pending') }}</strong>
            </p>
            <p class="mt-2">
                <x-link :href="route('admin.orders.status.edit', ['number' => $order->number])" class="text-sm">
                    {{ __('Update status') }}
                </x-link>
            </p>
        </div>
        <div class="text-right">
            <h2 class="font-medium">{{ config('app.name') }}</h2>
            <p class="text-sm text-gray-600">{{ url('/') }}</p>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold uppercase text-xs tracking-wider text-gray-500">{{ __('Ship to') }}</h3>
            @php($shipping = $order->shippingAddress ?? $order->addresses->firstWhere('address_type', 'shipping'))
            @if ($shipping)
                <p class="mt-2">{{ $shipping->first_name }} {{ $shipping->last_name }}</p>
                <p>{{ $shipping->address_line1 }}</p>
                @if ($shipping->address_line2)
                    <p>{{ $shipping->address_line2 }}</p>
                @endif
                <p>{{ $shipping->postal_code }} {{ $shipping->city }}, {{ $shipping->country_code }}</p>
                @if ($shipping->phone)
                    <p>{{ __('Phone') }}: {{ $shipping->phone }}</p>
                @endif
            @endif
        </div>
        <div>
            <h3 class="font-semibold uppercase text-xs tracking-wider text-gray-500">{{ __('Billing') }}</h3>
            @php($billing = $order->billingAddress ?? $order->addresses->firstWhere('address_type', 'billing'))
            @if ($billing)
                <p class="mt-2">{{ $billing->first_name }} {{ $billing->last_name }}</p>
                <p>{{ $billing->address_line1 }}</p>
                @if ($billing->address_line2)
                    <p>{{ $billing->address_line2 }}</p>
                @endif
                <p>{{ $billing->postal_code }} {{ $billing->city }}, {{ $billing->country_code }}</p>
                @if ($billing->phone)
                    <p>{{ __('Phone') }}: {{ $billing->phone }}</p>
                @endif
            @endif
        </div>
    </div>

    <table class="mt-8 w-full text-sm">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">{{ __('Product') }}</th>
                <th class="py-2">{{ __('SKU') }}</th>
                <th class="py-2 text-right">{{ __('Qty') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr class="border-b">
                    <td class="py-2 pr-4">{{ $item->product?->name ?? $item->name }}</td>
                    <td class="py-2 pr-4">{{ $item->sku }}</td>
                    <td class="py-2 text-right">{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-10 text-center print:hidden">
        <button onclick="window.print()" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-white">
            {{ __('Print') }}
        </button>
    </div>
</div>
