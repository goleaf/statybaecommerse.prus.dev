<div class="p-8 text-gray-900">
    <h1 class="text-2xl font-semibold">{{ __('ui.packing_slip') }}</h1>

    <div class="mt-6 grid grid-cols-2 gap-6">
        <div>
            <h2 class="font-medium">{{ __('messages.order') }} #{{ $order->number }}</h2>
            <p class="text-sm text-gray-600">{{ __('ui.placed_at') }}:
                {{ optional($order->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
            <p class="text-sm text-gray-600 mt-1">
                {{ __('messages.status') }}: <strong>{{ $order->status }}</strong> ·
                {{ __('messages.payment') }}: <strong>{{ $order->payment_status ?? __('ui.pending') }}</strong>
            </p>
            <p class="mt-2">
                <x-link :href="route('admin.orders.status.edit', ['number' => $order->number])" class="text-sm">
                    {{ __('ui.update_status') }}
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
            <h3 class="font-semibold uppercase text-xs tracking-wider text-gray-500">{{ __('ui.ship_to') }}</h3>
            @php($sa = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address)
            @if ($sa)
                <p class="mt-2">{{ $sa['first_name'] ?? '' }} {{ $sa['last_name'] ?? '' }}</p>
                <p>{{ $sa['street_address'] ?? ($sa['address_line1'] ?? '') }}</p>
                @if (!empty($sa['street_address_plus'] ?? ($sa['address_line2'] ?? null)))
                    <p>{{ $sa['street_address_plus'] ?? $sa['address_line2'] }}</p>
                @endif
                <p>{{ $sa['postal_code'] ?? '' }} {{ $sa['city'] ?? '' }},
                    {{ $sa['country_code'] ?? ($sa['country_name'] ?? ($sa['country'] ?? '')) }}</p>
                @if (!empty($sa['phone'] ?? ($sa['phone_number'] ?? null)))
                    <p>{{ __('messages.phone') }}: {{ $sa['phone'] ?? $sa['phone_number'] }}</p>
                @endif
            @endif
        </div>
        <div>
            <h3 class="font-semibold uppercase text-xs tracking-wider text-gray-500">{{ __('ui.billing') }}</h3>
            @php($ba = is_string($order->billing_address) ? json_decode($order->billing_address, true) : $order->billing_address)
            @if ($ba)
                <p class="mt-2">{{ $ba['first_name'] ?? '' }} {{ $ba['last_name'] ?? '' }}</p>
                <p>{{ $ba['street_address'] ?? ($ba['address_line1'] ?? '') }}</p>
                @if (!empty($ba['street_address_plus'] ?? ($ba['address_line2'] ?? null)))
                    <p>{{ $ba['street_address_plus'] ?? $ba['address_line2'] }}</p>
                @endif
                <p>{{ $ba['postal_code'] ?? '' }} {{ $ba['city'] ?? '' }},
                    {{ $ba['country_code'] ?? ($ba['country_name'] ?? ($ba['country'] ?? '')) }}</p>
                @if (!empty($ba['phone'] ?? ($ba['phone_number'] ?? null)))
                    <p>{{ __('messages.phone') }}: {{ $ba['phone'] ?? $ba['phone_number'] }}</p>
                @endif
            @endif
        </div>
    </div>

    <table class="mt-8 w-full text-sm">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">{{ __('messages.product') }}</th>
                <th class="py-2">{{ __('messages.sku') }}</th>
                <th class="py-2 text-right">{{ __('messages.qty') }}</th>
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
            {{ __('messages.print') }}
        </button>
    </div>
</div>
