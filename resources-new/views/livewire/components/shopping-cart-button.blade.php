<div class="relative shopping-cart-button" x-data="{ qty: {{ (int) ($cartTotalItems ?? 0) }} }" x-init="window.addEventListener('cartUpdated', e => { qty = e.detail?.quantity ?? qty })">
    <button
            wire:click="$dispatch('openPanel', { component: 'modals.shopping-cart' })"
            type="button"
            class="header-action-button"
            aria-haspopup="dialog" 
            aria-expanded="false" 
            aria-controls="hs-cart-right" 
            data-hs-overlay="#hs-cart-right">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
        </svg>
        <span class="cart-quantity" x-text="qty">{{ (int) ($cartTotalItems ?? 0) }}</span>
        <span class="sr-only">{{ __('items in cart, view cart') }}</span>
    </button>
</div>
