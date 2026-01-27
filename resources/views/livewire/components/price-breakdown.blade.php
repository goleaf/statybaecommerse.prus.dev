<div class="space-y-6">
    @if ($showSubtotal)
        <div class="flex items-center justify-between">
            <dt class="{{ $variant === 'mobile' ? 'text-gray-400' : 'text-gray-300' }}">
                {{ __('messages.subtotal') }}
            </dt>
            <dd>
                {{ \Illuminate\Support\Number::currency($this->subtotal, current_currency(), app()->getLocale()) }}
            </dd>
        </div>
    @endif

    @if ($showTaxes)
        <div class="flex items-center justify-between">
            <dt class="{{ $variant === 'mobile' ? 'text-gray-400' : 'text-gray-300' }}">
                {{ __('messages.taxes') }}
            </dt>
            <livewire:components.tax-price />
        </div>
    @endif

    @if ($showTotal)
        <div class="{{ $variant === 'mobile' ? '' : 'border-t border-white/10 pt-6' }}">
            <livewire:components.cart-total />
        </div>
    @endif
</div>