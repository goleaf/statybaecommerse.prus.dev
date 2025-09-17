<x-layouts.templates.app :title="__('Update Order')">
    <x-container class="py-8">
        <x-breadcrumbs :items="[['label' => __('Orders')], ['label' => $order->number], ['label' => __('Update')]]" />

        @if (session('status'))
            <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
        @endif

        <div class="flex items-center justify-between max-w-xl mb-4">
            <div class="text-sm text-gray-600">
                {{ __('Order') }} #{{ $order->number }} — {{ __('Current status') }}:
                <strong>{{ $order->status }}</strong>
            </div>
            <x-link :href="route('admin.orders.packing-slip', ['number' => $order->number])">{{ __('Packing slip') }}</x-link>
        </div>

        <div class="max-w-xl">
            <form method="POST" action="{{ route('admin.orders.status.update', ['number' => $order->number]) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">{{ __('Order status') }}</label>
                        <input type="text" name="status" value="{{ old('status', $order->status) }}"
                               class="mt-1 w-full border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Payment status') }}</label>
                        <input type="text" name="payment_status"
                               value="{{ old('payment_status', $order->payment_status) }}"
                               class="mt-1 w-full border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Note') }}</label>
                        <textarea name="note" rows="4" class="mt-1 w-full border-gray-300 rounded">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="mt-6">
                    <x-buttons.primary type="submit">{{ __('Save') }}</x-buttons.primary>
                </div>
            </form>
        </div>

        <div class="max-w-xl mt-10">
            <h2 class="text-lg font-medium mb-2">{{ __('Update tracking') }}</h2>
            <form method="POST" action="{{ route('admin.orders.tracking.update', ['number' => $order->number]) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">{{ __('Tracking number') }}</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number') }}"
                               class="mt-1 w-full border-gray-300 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">{{ __('Tracking URL') }}</label>
                        <input type="url" name="tracking_url" value="{{ old('tracking_url') }}"
                               class="mt-1 w-full border-gray-300 rounded" />
                    </div>
                </div>
                <div class="mt-4">
                    <x-buttons.primary type="submit">{{ __('Save tracking') }}</x-buttons.primary>
                </div>
            </form>
        </div>
    </x-container>
</x-layouts.templates.app>
