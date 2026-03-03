{{-- Order Invoice Component --}}

<div class="space-y-10">
    <div class="flex items-center justify-end">
        <x-buttons.default type="button" class="px-4 print:hidden" onclick="window.print()">
            {{ __('frontend.account.orders.print') }}
        </x-buttons.default>
    </div>

    <x-order.invoice :order="$order" />
</div>
