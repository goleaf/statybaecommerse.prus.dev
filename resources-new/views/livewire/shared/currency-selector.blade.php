<div>
    <div class="relative">
        <select wire:model="activeCurrencyCode" class="rounded-md border-gray-300 text-sm">
            @foreach ($currencies as $c)
                <option value="{{ $c['code'] }}">{{ $c['symbol'] }} {{ $c['code'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="hidden lg:ml-8 lg:flex">
        <button
                onclick="Livewire.dispatch('openPanel', { component: 'modals.zone-selector' })"
                type="button"
                class="flex items-center gap-2 text-gray-700 hover:text-gray-800">
            @if ($this->countryFlag)
                <img src="{{ $this->countryFlag }}" alt="country flag" class="block h-auto w-5 shrink-0" />
            @endif

            <span class="block text-sm font-medium">{{ current_currency() }}</span>
            <span class="sr-only">, {{ __('change currency') }}</span>
        </button>
    </div>
</div>
