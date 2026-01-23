{{-- Order Invoice Component --}}

<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('frontend.account.nav.title'), 'url' => route('account.index', ['locale' => app()->getLocale()])],
        ['label' => __('frontend.account.orders.title'), 'url' => route('account.orders', ['locale' => app()->getLocale()])],
        ['label' => __('frontend.account.orders.invoice')],
    ]" />

    <div class="flex items-center justify-end">
        <x-buttons.default type="button" class="px-4 print:hidden" onclick="window.print()">
            {{ __('frontend.account.orders.print') }}
        </x-buttons.default>
    </div>

    <x-order.invoice :order="$order" />
</div>
