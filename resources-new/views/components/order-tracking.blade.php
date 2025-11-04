@props([
    'order' => null,
    'title' => null,
    'subtitle' => null,
    'showDetails' => true,
    'showTimeline' => true,
    'showActions' => true,
])

@php
    $order = $order ?? new \App\Models\Order();
    $title = $title ?? __('Order Tracking');
    $subtitle = $subtitle ?? __('Track your order status and delivery information');

    // Order status timeline
    $statusTimeline = [
        'pending' => [
            'label' => __('Order Placed'),
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'blue',
        ],
        'confirmed' => [
            'label' => __('Order Confirmed'),
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'blue',
        ],
        'processing' => [
            'label' => __('Processing'),
            'icon' =>
                'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
            'color' => 'yellow',
        ],
        'shipped' => [
            'label' => __('Shipped'),
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'color' => 'purple',
        ],
        'delivered' => [
            'label' => __('Delivered'),
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'green',
        ],
        'cancelled' => [
            'label' => __('Cancelled'),
            'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'red',
        ],
    ];

    $currentStatus = $order->status ?? 'pending';
    $statusIndex = array_search($currentStatus, array_keys($statusTimeline));
@endphp

<div class="order-tracking" x-data="orderTracking()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            <p class="text-lg text-gray-600">{{ $subtitle }}</p>
        </div>

        {{-- Order Summary --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Order Number --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Order Number') }}</h3>
                    <p class="text-lg font-semibold text-gray-900">{{ $order->order_number ?? 'N/A' }}</p>
                </div>

                {{-- Order Date --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Order Date') }}</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $order->created_at ? $order->created_at->format('Y-m-d') : 'N/A' }}
                    </p>
                </div>

                {{-- Total Amount --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Total Amount') }}</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $order->total ? \Illuminate\Support\Number::currency($order->total, current_currency(), app()->getLocale()) : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Status Timeline --}}
        @if ($showTimeline)
            <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Order Status') }}</h2>

                <div class="relative">
                    {{-- Timeline Line --}}
                    <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <div class="space-y-8">
                        @foreach ($statusTimeline as $status => $config)
                            @php
                                $isActive = array_search($status, array_keys($statusTimeline)) <= $statusIndex;
                                $isCurrent = $status === $currentStatus;
                            @endphp

                            <div class="relative flex items-start gap-4">
                                {{-- Status Icon --}}
                                <div class="relative z-10 flex-shrink-0">
                                    <div
                                         class="w-16 h-16 rounded-full flex items-center justify-center
                                        {{ $isActive ? 'bg-' . $config['color'] . '-100' : 'bg-gray-100' }}">
                                        <svg class="w-8 h-8 {{ $isActive ? 'text-' . $config['color'] . '-600' : 'text-gray-400' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="{{ $config['icon'] }}"></path>
                                        </svg>
                                    </div>

                                    @if ($isCurrent)
                                        <div
                                             class="absolute -top-1 -right-1 w-6 h-6 bg-{{ $config['color'] }}-600 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Status Content --}}
                                <div class="flex-1 min-w-0">
                                    <h3
                                        class="text-lg font-semibold {{ $isActive ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $config['label'] }}
                                    </h3>

                                    @if ($isActive && $order->updated_at)
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $order->updated_at->format('M d, Y \a\t g:i A') }}
                                        </p>
                                    @endif

                                    @if ($isCurrent && $order->status_message)
                                        <p class="text-sm text-gray-700 mt-2">{{ $order->status_message }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Order Details --}}
        @if ($showDetails && $order->items && $order->items->count() > 0)
            <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Order Items') }}</h2>

                <div class="space-y-4">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl">
                            {{-- Product Image --}}
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img src="{{ $item->product->getFirstMediaUrl('images') ?? asset('images/placeholder-product.jpg') }}"
                                     alt="{{ $item->product->name }}"
                                     class="w-full h-full object-cover">
                            </div>

                            {{-- Product Details --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 mb-1">
                                    <a href="{{ route('product.show', $item->product->slug ?? $item->product) }}"
                                       class="hover:text-blue-600 transition-colors duration-200">
                                        {{ $item->product->name }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                                @if ($item->variant)
                                    <p class="text-sm text-gray-600">{{ __('Variant') }}: {{ $item->variant }}</p>
                                @endif
                            </div>

                            {{-- Price --}}
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">
                                    {{ \Illuminate\Support\Number::currency($item->price * $item->quantity, current_currency(), app()->getLocale()) }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ \Illuminate\Support\Number::currency($item->price, current_currency(), app()->getLocale()) }}
                                    {{ __('each') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Order Summary --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Subtotal') }}</span>
                            <span class="font-medium">
                                {{ \Illuminate\Support\Number::currency($order->subtotal ?? 0, current_currency(), app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Shipping') }}</span>
                            <span class="font-medium">
                                {{ \Illuminate\Support\Number::currency($order->shipping_cost ?? 0, current_currency(), app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Tax') }}</span>
                            <span class="font-medium">
                                {{ \Illuminate\Support\Number::currency($order->tax_amount ?? 0, current_currency(), app()->getLocale()) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <span>{{ __('Total') }}</span>
                            <span>
                                {{ \Illuminate\Support\Number::currency($order->total ?? 0, current_currency(), app()->getLocale()) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Shipping Information --}}
        @if ($order->shipping_address)
            <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Shipping Information') }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Shipping Address --}}
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ __('Shipping Address') }}</h3>
                        <div class="text-gray-600">
                            <p>{{ $order->shipping_address->name ?? 'N/A' }}</p>
                            <p>{{ $order->shipping_address->address_line_1 ?? 'N/A' }}</p>
                            @if ($order->shipping_address->address_line_2)
                                <p>{{ $order->shipping_address->address_line_2 }}</p>
                            @endif
                            <p>{{ $order->shipping_address->city ?? 'N/A' }},
                                {{ $order->shipping_address->state ?? 'N/A' }}
                                {{ $order->shipping_address->postal_code ?? 'N/A' }}</p>
                            <p>{{ $order->shipping_address->country ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Tracking Information --}}
                    @if ($order->tracking_number)
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Tracking Information') }}</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-gray-600">{{ __('Tracking Number') }}:</span>
                                    <p class="font-medium">{{ $order->tracking_number }}</p>
                                </div>
                                @if ($order->carrier)
                                    <div>
                                        <span class="text-sm text-gray-600">{{ __('Carrier') }}:</span>
                                        <p class="font-medium">{{ $order->carrier }}</p>
                                    </div>
                                @endif
                                @if ($order->estimated_delivery)
                                    <div>
                                        <span class="text-sm text-gray-600">{{ __('Estimated Delivery') }}:</span>
                                        <p class="font-medium">{{ $order->estimated_delivery->format('M d, Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Actions --}}
        @if ($showActions)
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Actions') }}</h2>

                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Download Invoice --}}
                    <button @click="downloadInvoice()"
                            class="flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        {{ __('Download Invoice') }}
                    </button>

                    {{-- Reorder --}}
                    <button @click="reorder()"
                            class="flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        {{ __('Reorder') }}
                    </button>

                    {{-- Contact Support --}}
                    <a href="{{ route('contact', ['locale' => app()->getLocale()]) ?? '/contact' }}"
                       class="flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        {{ __('Contact Support') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function orderTracking() {
        return {
            downloadInvoice() {
                // Download invoice logic
                const link = document.createElement('a');
                link.href = `/orders/{{ $order->id ?? 0 }}/invoice`;
                link.download = `invoice-{{ $order->order_number ?? 'order' }}.pdf`;
                link.click();
            },

            reorder() {
                // Reorder logic
                fetch('/orders/{{ $order->id ?? 0 }}/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '/cart';
                        } else {
                            alert('{{ __('Failed to reorder items. Please try again.') }}');
                        }
                    })
                    .catch(error => {
                        alert('{{ __('Network error. Please try again.') }}');
                    });
            }
        }
    }
</script>
