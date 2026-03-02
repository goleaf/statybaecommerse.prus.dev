{{-- Keep the Alpine badge in sync with the standard cart-updated browser event. --}}
<div
    class="relative ml-4 flow-root lg:ml-6"
    x-data="createCartButtonComponent({ quantity: {{ (int) ($cartTotalItems ?? 0) }} })"
    x-on:cart-count-changed.window="if (Number.isFinite(Number($event.detail?.quantity))) qty = Number($event.detail.quantity)"
>
    @php
        $cartUrl = Route::has('localized.cart.index')
            ? route('localized.cart.index', ['locale' => app()->getLocale()])
            : (Route::has('frontend.cart.index')
                ? route('frontend.cart.index')
                : route('cart.index'));
    @endphp

    <a href="{{ $cartUrl }}" wire:navigate class="group -m-2 flex items-center p-2">
        <svg
             class="size-6 shrink-0 text-gray-400 group-hover:text-gray-500"
             fill="none"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             aria-hidden="true">
            <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
        <span
            x-cloak
            x-show="qty > 0"
            class="absolute -top-1 -right-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-semibold leading-none text-white"
            x-text="qty > 99 ? '99+' : qty">
            {{ (int) ($cartTotalItems ?? 0) > 99 ? '99+' : (int) ($cartTotalItems ?? 0) }}
        </span>
        <span class="sr-only">{{ __('ui.items_in_cart_view_cart') }}</span>
    </a>
</div>
