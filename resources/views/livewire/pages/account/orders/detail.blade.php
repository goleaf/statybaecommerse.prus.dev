{{-- Order Detail Component --}}

@php
    $shippingStatus = strtolower((string) ($order->shipping?->status ?? 'pending'));
    $shippingStatusKey = 'messages.shipping_statuses.' . $shippingStatus;
    $shippingStatusLabel = __($shippingStatusKey);

    $paymentMethod = $order->payment_method;
    $paymentMethodLabel = $paymentMethod instanceof \App\Enums\PaymentMethod
        ? (string) $paymentMethod->getLabel()
        : __('frontend.order_summary.not_available');

    $paymentStatus = $order->payment_status instanceof \App\Enums\PaymentStatus
        ? (string) $order->payment_status->getLabel()
        : ucfirst(str_replace('_', ' ', (string) ($order->payment_status ?? 'pending')));

    $paymentState = $order->payment_state instanceof \App\Enums\OrderPaymentState
        ? (string) $order->payment_state->getLabel()
        : null;

    if ($paymentState === null || trim($paymentState) === '') {
        $paymentStateValue = strtolower(trim((string) ($order->payment_state instanceof \BackedEnum
            ? $order->payment_state->value
            : ($order->payment_state ?? 'created'))));

        $paymentStateKey = 'enums.order_payment_state.' . $paymentStateValue;
        $paymentState = __($paymentStateKey);

        if ($paymentState === $paymentStateKey) {
            $paymentState = ucfirst(str_replace('_', ' ', $paymentStateValue));
        }
    }

    $shippingMethod = (string) ($order->shipping?->shipping_method ?? '');
    $carrierName = (string) ($order->shipping?->carrier_name ?? '');
    $trackingNumber = (string) ($order->shipping?->tracking_number ?? '');
    $trackingUrl = (string) ($order->shipping?->tracking_url ?? '');
    $shippingAddress = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;

    $documentsByType = collect($documentsByType ?? []);
    $documentsTotal = $documentsByType->sum(
        static fn ($documents): int => $documents instanceof \Illuminate\Support\Collection ? $documents->count() : 0
    );

    if ($shippingStatusLabel === $shippingStatusKey) {
        $shippingStatusLabel = ucfirst(str_replace('_', ' ', $shippingStatus));
    }
@endphp

<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.order_detail.title') }}</h1>
    </header>

    <div class="space-y-6">
        <div class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2 lg:grid-cols-5">
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-700">
                    {{ __('frontend.account.order_detail.order_number_label') }}
                </dt>
                <dd class="mt-1 font-semibold uppercase text-gray-900">
                    {{ $order->number ?? __('frontend.account.order_detail.order_number_value') }}
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">
                    {{ __('frontend.account.order_detail.placed_on') }}
                </dt>
                <dd class="mt-1 text-gray-600 capitalize">
                    <time datetime="{{ format_datetime($order->created_at) }}">
                        {{ format_datetime($order->created_at) }}
                    </time>
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">
                    {{ __('frontend.account.order_detail.total') }}
                </dt>
                <dd class="mt-1 text-base font-semibold text-gray-900">
                    {{ app_money_format($order->total ?? 0.0, $order->currency) }}
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('frontend.account.order_detail.status') }}</dt>
                <dd class="mt-1 text-gray-600">
                    <x-order.status :status="$order->status" />
                </dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.shipping_status') }}</dt>
                <dd class="mt-1 text-gray-600">
                    {{ $shippingStatusLabel }}
                </dd>
            </div>
        </div>

        <div class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.payment_method') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $paymentMethodLabel }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.payment_status') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $paymentStatus }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.shipping_method') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $shippingMethod !== '' ? $shippingMethod : __('frontend.order_summary.not_available') }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.carrier') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $carrierName !== '' ? $carrierName : __('frontend.order_summary.not_available') }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.tracking_number') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $trackingNumber !== '' ? $trackingNumber : __('frontend.order_summary.not_available') }}</dd>
            </div>
            <div class="text-sm">
                <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.payment_state') }}</dt>
                <dd class="mt-1 text-gray-600">{{ $paymentState }}</dd>
            </div>
            @if ($trackingUrl !== '')
                <div class="text-sm sm:col-span-2">
                    <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.tracking_url') }}</dt>
                    <dd class="mt-1 text-gray-600">
                        <a href="{{ $trackingUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">
                            {{ $trackingUrl }}
                        </a>
                    </dd>
                </div>
            @endif
            @if (is_array($shippingAddress) && ! empty($shippingAddress['delivery_place_name']))
                <div class="text-sm sm:col-span-2">
                    <dt class="font-medium tracking-tighter text-gray-900">{{ __('messages.shipping') }}</dt>
                    <dd class="mt-1 text-gray-600">{{ $shippingAddress['delivery_place_name'] }}</dd>
                    @if (! empty($shippingAddress['delivery_place_address']))
                        <dd class="text-gray-600">{{ $shippingAddress['delivery_place_address'] }}</dd>
                    @endif
                </div>
            @endif
        </div>

        <x-order.items :items="$order->items" :currency_code="$order->currency" />

        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900">{{ __('frontend.account.order_detail.summary') }}</h2>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <x-order.summary :order="$order" />
            </div>
        </div>

        <div class="space-y-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900">{{ __('frontend.account.documents_table.title') }}</h2>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                @if ($documentsTotal === 0)
                    <p class="text-sm text-gray-500">{{ __('frontend.account.documents_empty') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach ($documentsByType as $documentType => $documentsOfType)
                            @if (! $documentsOfType instanceof \Illuminate\Support\Collection || $documentsOfType->isEmpty())
                                @continue
                            @endif

                            @php
                                $documentTypeNormalized = strtolower(trim((string) $documentType));
                                $documentTypeKey = 'enums.invoice_type.' . $documentTypeNormalized;
                                $documentTypeLabel = __($documentTypeKey);

                                if ($documentTypeLabel === $documentTypeKey) {
                                    $documentTypeLabel = $documentTypeNormalized !== '' && $documentTypeNormalized !== 'other'
                                        ? \Illuminate\Support\Str::upper($documentTypeNormalized)
                                        : __('frontend.order_summary.not_available');
                                }
                            @endphp

                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $documentTypeLabel }}</h3>
                                <div class="space-y-2">
                                    @foreach ($documentsOfType as $invoice)
                                        @php
                                            $downloadUrl = $invoice->status === \App\Models\OrderInvoice::STATUS_READY ? $invoice->downloadUrl() : null;
                                            $statusKey = 'messages.invoice_status_' . \Illuminate\Support\Str::snake((string) $invoice->status);
                                            $statusLabel = __($statusKey);

                                            if ($statusLabel === $statusKey) {
                                                $statusLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $invoice->status));
                                            }
                                        @endphp
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 p-3">
                                            <div class="space-y-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $invoice->full_number ?: ($invoice->external_invoice_id ?: __('ui.invoice')) }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ __('frontend.account.documents_table.status') }}: {{ $statusLabel }}
                                                    @if ($invoice->generated_at)
                                                        · {{ __('frontend.account.documents_table.generated_at') }}: {{ format_datetime($invoice->generated_at) }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if (is_string($downloadUrl) && $downloadUrl !== '')
                                                <a href="{{ $downloadUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-blue-600 hover:underline">
                                                    {{ __('frontend.account.documents_table.download') }}
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
