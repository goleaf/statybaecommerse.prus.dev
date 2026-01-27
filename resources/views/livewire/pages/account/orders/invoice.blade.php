{{-- Order Invoice Component --}}

<div class="space-y-10">
    <x-breadcrumbs :items="[
        ['label' => __('messages.frontend'), 'url' => route('account.index', ['locale' => app()->getLocale()])],
        ['label' => __('messages.frontend'), 'url' => route('account.orders', ['locale' => app()->getLocale()])],
        ['label' => __('messages.frontend')],
    ]" />

    <div class="flex items-center justify-end">
        <x-buttons.default type="button" class="px-4 print:hidden" onclick="window.print()">
            {{ __('messages.frontend') }}
        </x-buttons.default>
    </div>

    <x-order.invoice :order="$order" />
</div>
